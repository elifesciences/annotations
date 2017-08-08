# 2. Do not store annotations data

Date: 2017-08-08

## Status

Accepted

## Context

We only have requirements for pages showing set of annotations supported by the existing Hypothesis API.

Hence, we have no need for special APIs that index the annotations content.

We are uncertain about the performance of the Hypothesis API, whether it will need to be cached and for how long.

## Decision

Do not store in this service annotations data, in any local database or cache.

## Consequences

Always load data asking directly the Hypothesis API.

Do not introduce caches as that can be dealt with at an higher level.

