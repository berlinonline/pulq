# 1.0.0

* moved from git submodule to composer library
* showcase project is now a template for new projects
* exchanged ruflin/elastica for elasticsearch/elasticsearch client
* now compatible with elasticsearch >=1.0
* changed elasticsearch queries to filters
* make db - sets up a new ES index
* make fixtures - load a prepared dataset into the index for development
* make reindex - adds a new ES index and reindexes all documents for the current index into it
* generally revised Makefile
* removed unecessary dependencies
* HTTPS URLs can be triggered via header ("HTTPS: true" or "X-SSL: true")
