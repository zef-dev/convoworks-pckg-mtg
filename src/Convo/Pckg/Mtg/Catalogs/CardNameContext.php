<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg\Catalogs;

use Convo\Core\Workflow\AbstractWorkflowComponent;
use Convo\Core\Workflow\ICatalogSource;
use Convo\Core\Workflow\IServiceContext;

class CardNameContext extends AbstractWorkflowComponent implements IServiceContext, ICatalogSource
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

    public function __construct($properties, $catalogName, $httpFactory)
    {
        parent::__construct($properties);

        $this->_version = $properties['version'];

        $this->_componentId = $catalogName;

        $this->_httpFactory = $httpFactory;
    }

    public function getCatalogVersion()
    {
        return $this->_catalog->getCatalogVersion();
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
