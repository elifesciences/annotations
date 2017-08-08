# 1. Hide all communication with Hypothesis

Date: 2017-08-08

## Status

Accepted

## Context

The eLife API needs to integrate with a [Hypothesis](https://web.hypothes.is/) instance to serve user-produced content in the form of annotations over existing data.

We are at risk of side-effects coming from Hypothesis propagating to journal and the other API users:

- changes in the API response body
- high latency
- server-side errors and general unavailability

We don't want to stick to the Hypothesis data model but we'd rather translate it for consistency with the rest of [api-raml](https://github.com/elifesciences/api-raml).

## Decision

We want this service to hide all communication with Hypothes.is, providing a translation layer.

## Consequences

Every HTTP call to the Hypothesis instance should be performed from this service, either directly or with the help of a library.

Credentials of any kind should be configured within this service and not exposed to API users.
