<?php

namespace App\Tests\Unit\Service;

use App\Service\AdresseApiService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Client de la Base Adresse Nationale testé avec MockHttpClient : aucun appel réseau.
 */
final class AdresseApiServiceTest extends TestCase
{
    private function service(MockHttpClient $client, ?LoggerInterface $logger = null): AdresseApiService
    {
        return new AdresseApiService($client, $logger ?? new NullLogger(), 'https://ban.test/', 2.0);
    }

    private function reponse(array ...$proprietes): JsonMockResponse
    {
        return new JsonMockResponse([
            'type'     => 'FeatureCollection',
            'features' => array_map(fn (array $p) => ['type' => 'Feature', 'properties' => $p], $proprietes),
        ]);
    }

    public function testNormaliseLesSuggestions(): void
    {
        $client = new MockHttpClient($this->reponse([
            'label'    => '8 Boulevard du Port 95000 Cergy',
            'name'     => '8 Boulevard du Port',
            'postcode' => '95000',
            'city'     => 'Cergy',
            'type'     => 'housenumber',
        ]));

        self::assertSame([[
            'label'      => '8 Boulevard du Port 95000 Cergy',
            'rue'        => '8 Boulevard du Port',
            'codePostal' => '95000',
            'ville'      => 'Cergy',
            'pays'       => 'France',
        ]], $this->service($client)->search('8 boulevard du port'));
    }

    public function testInterrogeLeEndpointSearchAvecLaRequeteNettoyeeEtLaLimite(): void
    {
        $appel  = null;
        $client = new MockHttpClient(function (string $method, string $url) use (&$appel) {
            $appel = [$method, $url];
            return $this->reponse();
        });

        $this->service($client)->search('  8 bd du port  ', 3);

        [$method, $url] = $appel;
        self::assertSame('GET', $method);
        self::assertStringStartsWith('https://ban.test/search/?', $url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        self::assertSame(['q' => '8 bd du port', 'limit' => '3'], $query);
    }

    #[DataProvider('limites')]
    public function testBorneLaLimiteDeResultats(int $demandee, string $envoyee): void
    {
        $url    = null;
        $client = new MockHttpClient(function (string $method, string $u) use (&$url) {
            $url = $u;
            return $this->reponse();
        });

        $this->service($client)->search('rue de la paix', $demandee);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        self::assertSame($envoyee, $query['limit']);
    }

    public static function limites(): iterable
    {
        yield 'au-dessus du maximum' => [50, '10'];
        yield 'zéro'                 => [0, '1'];
        yield 'négative'             => [-3, '1'];
    }

    public function testUneRequeteTropCourteNAppellePasLeService(): void
    {
        $client = new MockHttpClient(fn () => self::fail('Aucun appel HTTP attendu.'));

        self::assertSame([], $this->service($client)->search('  ab '));
        self::assertSame(0, $client->getRequestsCount());
    }

    public function testUneCommuneNAPasDeRue(): void
    {
        $client = new MockHttpClient($this->reponse([
            'label' => 'Cergy', 'name' => 'Cergy', 'postcode' => '95000', 'city' => 'Cergy', 'type' => 'municipality',
        ]));

        $suggestions = $this->service($client)->search('cergy');

        self::assertSame('', $suggestions[0]['rue']);
        self::assertSame('Cergy', $suggestions[0]['ville']);
    }

    public function testIgnoreLesResultatsSansLibelle(): void
    {
        $client = new MockHttpClient($this->reponse(['name' => 'Sans libellé'], ['label' => 'Paris', 'city' => 'Paris']));

        self::assertCount(1, $this->service($client)->search('paris'));
    }

    public function testServiceInjoignableRenvoieUneListeVideEtJournalise(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['error' => 'Connection refused']));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        self::assertSame([], $this->service($client, $logger)->search('8 boulevard du port'));
    }

    public function testErreurServeurRenvoieUneListeVide(): void
    {
        $client = new MockHttpClient(new MockResponse('Erreur interne', ['http_code' => 500]));

        self::assertSame([], $this->service($client)->search('8 boulevard du port'));
    }

    public function testReponseIllisibleRenvoieUneListeVide(): void
    {
        $client = new MockHttpClient(new MockResponse('<html>maintenance</html>', [
            'response_headers' => ['content-type: text/html'],
        ]));

        self::assertSame([], $this->service($client)->search('8 boulevard du port'));
    }
}
