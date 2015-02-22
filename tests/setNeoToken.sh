#!/bin/bash
curl -v -X POST --user neo4j:neo4j -d '{"password": "veryCoolMax"}' -H 'Content-Type: application/json' -i http://localhost:7474/user/neo4j/password