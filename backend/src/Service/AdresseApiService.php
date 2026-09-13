<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client de la Base Adresse Nationale (api-adresse.data.gouv.fr).
 *
 * Dégradation silencieuse : si le service est indisponible, lent ou répond mal,
 * la recherche renvoie une liste vide et la saisie manuelle reste possible.
 */
class AdresseApiService
{
    public const MIN_QUERY_LENGTH = 3;
    public const MAX_LIMIT        = 10;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $baseUrl,
        private float $timeout
    ) {}

    /**
     * @return list<array{label: string, rue: string, codePostal: string, ville: string, pays: string}>
     */
    public function search(string $query, int $limit = 5): array
    {
        $query = trim($query);
        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $limit = max(1, min(self::MAX_LIMIT, $limit));

        try {
            $response = $this->httpClient->request('GET', rtrim($this->baseUrl, '/') . '/search/', [
                'query'   => ['q' => $query, 'limit' => $limit],
                'timeout' => $this->timeout,
            ]);

            $features = $response->toArray()['features'] ?? [];
        } catch (ExceptionInterface $e) {
            $this->logger->warning('Base Adresse Nationale indisponible : {message}', ['message' => $e->getMessage()]);
            return [];
        }

        $suggestions = [];
        foreach ($features as $feature) {
            $p = $feature['properties'] ?? [];
            if (empty($p['label'])) {
                continue;
            }

            $suggestions[] = [
                'label'      => $p['label'],
                // Pour une adresse précise, « name » vaut « 8 Boulevard du Port » ; pour une commune, son nom.
                'rue'        => ($p['type'] ?? '') === 'municipality' ? '' : ($p['name'] ?? ''),
                'codePostal' => $p['postcode'] ?? '',
                'ville'      => $p['city'] ?? '',
                'pays'       => 'France',
            ];
        }

        return $suggestions;
    }
}
