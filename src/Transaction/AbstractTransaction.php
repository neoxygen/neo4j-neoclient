<?php

namespace GraphAware\Neo4j\Client\Transaction;

use GraphAware\Neo4j\Client\Exception\InvalidArgumentException;

abstract class AbstractTransaction implements TransactionInterface
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var bool
     */
    protected $isCommitted = false;

    /**
     * @var bool
     */
    protected $isRunning = false;

    /**
     * @var bool
     */
    protected $isRolledBack = false;

    /**
     * @param string $mode
     * @param string|null $tag
     */
    public function __construct($mode = TransactionInterface::TRANSACTION_WRITE, $tag = null)
    {
        $this->setMode($mode);
        $this->tag = $tag;
    }

    /**
     * @param string $mode
     */
    protected function setMode($mode)
    {
        if ($mode !== TransactionInterface::TRANSACTION_WRITE && $mode !== TransactionInterface::TRANSACTION_READ) {
            throw new InvalidArgumentException(sprintf('The mode "%s" is not a valid Transactional mode', $mode));
        }

        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return boolean
     */
    public function isCommitted()
    {
        return $this->isCommitted;
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * @return boolean
     */
    public function isRolledBack()
    {
        return $this->isRolledBack;
    }


}