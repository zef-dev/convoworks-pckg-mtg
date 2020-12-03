<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg;

use Convo\Core\Util\IHttpFactory;
use Convo\Core\Util\StrUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MtgRestHandler implements RequestHandlerInterface
{
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

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$info = new \Convo\Core\Rest\RequestInfo( $request);

		$this->_logger->debug( 'Got info ['.$info.']');

		if ($info->get() && $info->route('mtg/entities'))
		{
			return $this->_handleMtgEntitiesGet($request);
		}

		throw new \Convo\Core\Rest\NotFoundException( 'Could not map ['.$info.']');
	}

	private function _handleMtgEntitiesGet(ServerRequestInterface $request)
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
			$formatted['values'][] = [
				'id' => StrUtil::uuidV4(),
				'name' => [
					'value' => $name
				]
			];
		}

		return $this->_httpFactory->buildResponse($formatted);
	}
}
