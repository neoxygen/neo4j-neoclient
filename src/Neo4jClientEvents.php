<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

final class Neo4jClientEvents
{
    const NEO4J_PRE_RUN = 'neo4j.pre_run';

    const NEO4J_POST_RUN = 'neo4j.post_run';

    const NEO4J_ON_FAILURE = 'neo4j.on_failure';
}