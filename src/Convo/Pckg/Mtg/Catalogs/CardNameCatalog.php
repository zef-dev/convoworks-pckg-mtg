<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg\Catalogs;

use Convo\Core\Util\IHttpFactory;
use Convo\Core\Util\StrUtil;

class CardNameCatalog implements \Convo\Core\Workflow\ICatalogSource
{
    const CATALOG_VERSION = "2";

    const SYNONYM_MAP = [
        'Syr' => 'Sir',
        'Ardenvale' => 'Arden Vale',
        'Hengehammer' => 'Henge Hammer',
        'Locthwain' => 'Loch Twain',
        'Pathrazer' => 'Path Razor'
    ];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Convo\Core\Util\IHttpFactory
     */
    private $_httpFactory;

    public function __construct($logger, $httpFactory)
    {
        $this->_logger = $logger;
        $this->_httpFactory = $httpFactory;
    }

    public function getCatalogVersion()
    {
        return self::CATALOG_VERSION;
    }

    public function getCatalogValues($platform)
    {
        switch ($platform)
        {
            case 'amazon':
                return $this->_getAmazonFormattedNames();
            case 'dialogflow':
                return $this->_getDialogflowFormattedNames();
            default:
                throw new \Exception("Unexpected platform [$platform]");
        }
    }

    private function _getAmazonFormattedNames()
    {
        $client = $this->_httpFactory->getHttpClient();
        $request = $this->_httpFactory->buildRequest(
            IHttpFactory::METHOD_GET,
            'https://api.scryfall.com/catalog/card-names'
        );

        $res = $client->sendRequest($request);

        $body = json_decode($res->getBody()->__toString(), true);
        $names = $body['data'];

        $this->_logger->debug('Got ['.count($names).'] card names');

        $formatted = [
            'values' => []
        ];

        foreach ($names as $name) {
            if (strlen($name) > 140) {
                $this->_logger->warning("Card name [$name] exceeds 140 characters. Skipping.");
                continue;
            }

            $card = [
                'id' => strtoupper(StrUtil::slugify($name)),
                'name' => [
                    'value' => $name
                ]
            ];

            $synonym = $this->_createSynonymFromName($name);
            if ($name !== $synonym)
            {
                $card['synonyms'][] = $synonym;
            }

            $formatted['values'][] = $card;
        }
        return $formatted;
    }

    private function _getDialogflowFormattedNames()
    {
        $client = $this->_httpFactory->getHttpClient();

        $query = $this->_toQuery([
            'q' => 'format:standard'
        ], false, true);

        $url = 'https://api.scryfall.com/cards/search?'.$query;

        $this->_logger->debug('Will GET from ['.$url.']');

        $request = $this->_httpFactory->buildRequest(
            IHttpFactory::METHOD_GET,
            $url
        );

        $res = $client->sendRequest($request);

        $body = json_decode($res->getBody()->__toString(), true);
        $cards = $body['data'];

        $this->_logger->debug('Got ['.count($cards).'] cards');

        return array_map(function($card) { return $card['name']; }, $cards);
    }

    private function _createSynonymFromName($name)
    {
        $parts = explode(' ', $name);

        foreach ($parts as &$part)
        {
            if (isset(self::SYNONYM_MAP[$part]))
            {
                $part = self::SYNONYM_MAP[$part];
            }
        }

        return implode(' ', $parts);
    }

    private function _toQuery($array, $shouldPrefixWithQuestionMark = false, $urlencode = false)
    {
        $query = '';
        $pairs = [];

        if ($shouldPrefixWithQuestionMark) {
            $query .= '?';
        }

        foreach ($array as $key => $val) {
            $pairs[] .= "$key=".($urlencode ? urlencode($val) : $val);
        }

        $query .= implode('&', $pairs);

        $this->_logger->debug("Final query [$query]");

        return $query;
    }
};
