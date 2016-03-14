<?php

namespace GraphAware\Neo4j\Client\Result;

use GraphAware\Common\Result\ResultCollection as BaseResultCollection;

class ResultCollection extends BaseResultCollection
{
    protected $tag;

    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    public function getTag()
    {
        return $this->tag;
    }
}