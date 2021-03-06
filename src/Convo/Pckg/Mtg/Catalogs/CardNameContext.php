<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg\Catalogs;

class CardNameContext implements \Convo\Core\Workflow\IServiceContext
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Convo\Core\Util\IHttpFactory
     */
    private $_httpFactory;

    private $_componentId;

    /**
     * @var \Convo\Core\Workflow\ICatalogSource
     */
    private $_catalog;

    public function __construct($catalogName, $logger, $httpFactory)
    {
        $this->_componentId = $catalogName;

        $this->_logger = $logger;

        $this->_httpFactory = $httpFactory;
    }

    public function getId()
    {
        return $this->_componentId;
    }

    public function init()
    {
        $this->_logger->debug('CardNameContext init');
        $this->_catalog = new CardNameCatalog($this->_logger, $this->_httpFactory);
    }

    public function getComponent()
    {
        if (!$this->_catalog) {
            $this->init();
        }

        return $this->_catalog;
    }

    // UTIL
    public function __toString()
    {
        return get_class($this).'[]';
    }
}
