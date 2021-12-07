<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg;

use Convo\Core\Factory\AbstractPackageDefinition;
use Convo\Core\Factory\ComponentDefinition;
use Convo\Core\Factory\IComponentFactory;
use Convo\Core\Util\StrUtil;
use Convo\Pckg\Mtg\Catalogs\CardNameContext;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class MtgPackageDefinition extends AbstractPackageDefinition
{
	const NAMESPACE = 'mtg';

	/**
	 * @var \Convo\Core\Util\IHttpFactory
	 */
	private $_httpFactory;

	public function __construct(
		\Psr\Log\LoggerInterface $logger,
        \Convo\Core\Util\IHttpFactory $httpFactory
	)
	{
		$this->_httpFactory = $httpFactory;

		parent::__construct($logger, self::NAMESPACE, __DIR__);
	}

	public function getFunctions()
	{
		$functions = [];

		$functions[] = new ExpressionFunction(
			'parse_mana',
			function ($mana) {
				return sprintf('(is_string(%1$s) ? parse_mana(%1$s) : %1$s', $mana);
			},
			function ($args, $mana) {
				$map = [
					'W' => 'white',
					'B' => 'black',
					'G' => 'green',
					'R' => 'red',
					'U' => 'blue',
					// HYBRID
					'W/U' => 'white or blue',
					'U/B' => 'blue or black',
					'B/R' => 'black or red',
					'R/G' => 'red or green',
					'G/W' => 'green or white',
					'W/B' => 'white or black',
					'U/R' => 'blue or red',
					'B/G' => 'black or green',
					'R/W' => 'red or white',
					'G/U' => 'green or blue'
				];

				$pattern = '/{(.*?)}/';
				$matches = [];

				preg_match_all($pattern, $mana, $matches);
				$count = array_count_values($matches[1]);

				$ret = [];

				foreach ($count as $symbol => $n) {
					$ret[] = isset($map[$symbol]) ? $n.' '.$map[$symbol] : $symbol.' colorless';
				}

				return StrUtil::concenateWithHumanTouch($ret, ' and ').' mana';
			}
		);

		return $functions;
	}

	protected function _initDefintions()
	{
	    return [
			new ComponentDefinition(
				$this->getNamespace(),
                '\Convo\Pckg\Mtg\Catalogs\CardNameContext',
				'Card Name Catalog',
				'Use a catalog entity for card names (currently only available on Amazon Alexa)',
				[
					'properties' => [
						'version' => [
							'editor_type' => 'text',
							'editor_properties' => [],
							'name' => 'Version',
							'defaultValue' => '',
							'description' => 'Catalog version for propagating new values.',
							'valueType' => 'string'
						]
					],
					'_preview_angular' => array(
						'type' => 'html',
						'template' => '<div class="code">' .
							'<span class="statement">USE CATALOG ENTITY</span> <b>CardName</b>'
					),
				    '_class_aliases' => ['\Convo\Pckg\Mtg\CardNameCatalog'],
					'_workflow' => 'datasource',
					'_factory' => new class ($this->_logger, $this->_httpFactory) implements IComponentFactory
					{
						private $_logger;
						private $_httpFactory;

						public function __construct($logger, $httpFactory)
						{
							$this->_logger = $logger;
							$this->_httpFactory = $httpFactory;
						}

						public function createComponent($properties, $service)
						{
							return new CardNameContext(
								$properties,
								'CardNameCatalog',
								$this->_httpFactory
							);
						}
					}
				]
			)
		];
	}
}
