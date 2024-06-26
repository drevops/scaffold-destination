# Database Search

This module provides a database-based implementation of the Search API. The
database and target to use for storing and accessing the indexes can be selected
when creating a new server.

All Search API data types are supported by using appropriate SQL data types for
their respective columns.

Simple 2-word phrase searching is supported by surrounding the keywords in
double quotes.

## Supported optional features

- `search_api_autocomplete`
  Introduced by module: [search_api_autocomplete]
  Lets you add autocompletion capabilities to search forms on the site. (See
  also "Hidden variables" below for backend-specific customization.)
  NOTE: Due to internal database restrictions, this will perform significantly
  better if only a single field is used for autocompletion.
- `search_api_facets`
  Introduced by module: [facets]
  Allows you to create faceted searches for dynamically filtering search
  results.
- `search_api_facets_operator_or`
  Introduced by module: [facets]
  Allows the use of the "OR" operator for facets.

[search_api_autocomplete]: https://www.drupal.org/project/search_api_autocomplete
[facets]: https://www.drupal.org/project/facets

If you feel some backend option is missing, or have other ideas for improving
this implementation, file a feature request in the project's [issue queue],
using the “Database search” component.

[issue queue]: https://www.drupal.org/project/issues/search_api

## Known problems

Using facets and autocomplete suggestions with a database server will only work
if the database user Drupal is using has the `CREATE TEMPORARY TABLES`
permission (or similar, in DBMSs other than MySQL).

Location search is available when MySQL version 5.7+ or one of its equivalents
are in use. This usually requires the [search_api_location] module. Location
search is unavailable for PostgreSQL and SQLite at the moment.

[search_api_location]: https://www.drupal.org/project/search_api_location

## Developer information

Database queries for searches with this module are tagged with
`search_api_db_search` to allow easy altering. As metadata, such database
queries will have the Search API query object set as `search_api_query`, and the
field settings of the server for the corresponding search index as
`search_api_db_fields`.

## Hidden configuration

- `search_api_db.settings.autocomplete_max_occurrences` (default: `0.9`)
  By default, keywords that occur in more than 90% of results are ignored for
  autocomplete suggestions. This setting lets you modify that behavior by
  providing your own ratio. Use 1 or greater to use all suggestions.
