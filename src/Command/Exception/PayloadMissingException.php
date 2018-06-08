<?php
/**
 * Created by PhpStorm.
 * User: jindun
 * Date: 07/06/18
 * Time: 15:03
 */

namespace TheAentMachine\AentDockerCompose\Command\Exception;

class PayloadMissingException extends EventCommandException
{
    /** @var string */
    private $payloadNeededEvent;

    /**
     * PayloadMissingException constructor.
     * @param string $payloadNeededEvent
     */
    public function __construct(string $payloadNeededEvent)
    {
        $this->payloadNeededEvent = $payloadNeededEvent;
        parent::__construct('payload missing for the event : ' . $this->$payloadNeededEvent);
    }
}
