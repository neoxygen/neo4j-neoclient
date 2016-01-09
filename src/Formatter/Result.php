<?php

namespace Neoxygen\NeoClient\Formatter;

use GraphAware\Common\Result\AbstractRecordCursor;
use Neoxygen\NeoClient\Formatter\Type\Node;
use Neoxygen\NeoClient\Formatter\Type\Relationship;

class Result extends AbstractRecordCursor
{
    /**
     * @var \GraphAware\Common\Result\RecordViewInterface[]
     */
    protected $records = [];

    /**
     * @var string[]
     */
    protected $fields = [];

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function pushRecord($data)
    {
        $mapped = $this->array_map_deep($data);
        $this->records[] = new RecordView($this->fields, $mapped);
    }

    /**
     * @return \GraphAware\Common\Result\RecordViewInterface[]
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return \GraphAware\Common\Result\RecordViewInterface|null
     */
    public function getRecord()
    {
        return !empty($this->records) ? $this->records[0] : null;
    }

    public function hasRecord()
    {
        return !empty($this->records);
    }

    public function position()
    {
        // TODO: Implement position() method.
    }

    public function skip()
    {
        // TODO: Implement skip() method.
    }

    private function array_map_deep(array $array)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                if (array_key_exists('metadata', $v) && isset($v['metadata']['labels'])) {
                    $array[$k] = new Node($v['metadata']['id'], $v['metadata']['labels'], $v['data']);
                } elseif (array_key_exists('start', $v) && array_key_exists('type', $v)) {
                    $array[$k] = new Relationship(
                        $v['metadata']['id'],
                        $v['type'],
                        $this->extractIdFromRestUrl($v['start']),
                        $this->extractIdFromRestUrl($v['end']),
                        $v['data']
                        );
                } else {
                    $array[$k] = $this->array_map_deep($v);
                }
            }
        }

        return $array;
    }

    private function extractIdFromRestUrl($url)
    {
        $expl = explode('/', $url);
        $v = $expl[count($expl)-1];

        return (int) $v;
    }

}