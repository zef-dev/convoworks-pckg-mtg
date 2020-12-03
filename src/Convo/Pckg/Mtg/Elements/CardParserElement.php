<?php declare(strict_types=1);

namespace Convo\Pckg\Mtg\Elements;

use Convo\Core\Workflow\IConvoRequest;
use Convo\Core\Workflow\IConvoResponse;

class CardParserElement extends \Convo\Core\Workflow\AbstractWorkflowComponent implements \Convo\Core\Workflow\IConversationElement
{
	/**
	 * @var array
	 */
	private $_cardData;

	/**
	 * @var string
	 */
	private $_name;

	/**
	 * @var string
	 */
	private $_mode;

	public function __construct($properties)
	{
		parent::__construct($properties);

		if (!isset($properties['card_data'])) {
			throw new \Exception('Missing required card data.');
		}

		$this->_cardData = $properties['card_data'];

		$this->_name = $properties['name'] ?? 'parsed_card';
		$this->_mode = $properties['mode'];
	}


	public function read(IConvoRequest $request, IConvoResponse $response)
	{
		// TODO: Implement read() method.
	}

	// UTIL

	public function __toString()
	{
		return parent::__toString().'['.$this->_mode.']';
	}
}
