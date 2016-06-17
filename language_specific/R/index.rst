=====================
R Specific Notes
=====================

Rxapian provides an R interface to Xapian search engine library.

Rxapian enables content of  a data frame to be indexed with xapian_index function. Following example explains how to build a simple search system based on museum catalogue data released under the `Creative Commons Attribution-NonCommercial-ShareAlike <https://creativecommons.org/licenses/by-nc-sa/3.0/>`_ license by the `Science Museum in London, UK <http://www.sciencemuseum.org.uk/>`_.

Note: For detailed explanations, refer to the section `A practical example <http://getting-started-with-xapian.readthedocs.io/en/latest/practical_example/index.html>`_ in `Getting started guide <http://getting-started-with-xapian.readthedocs.io/en/latest/index.html>`_ of Xapian.

Indexing
##########

xapian_index function takes the following format:

**xapian_index(database, dataFrame, idColumn, indexFields, filterFields, valueSlots, stemmer)**

**database:** This argument requires the path to a Xapian::Database. If a database is not already present in the given path, a new database will be created.

**dataFrame:** This arguments requires a data frame whose content will be indexed. Each row in the data frame will be used to create a Xapian::Document during indexing.

In order to index the CSV containing museum catalog data with RXapian, it should be converted to a data frame. This can be easily done with read.csv(“path/to/csv”) command. 

**idColumn:** This argument requires the index of a column in the database whose row value will be used as a unique identifier to each document(row). [Prefix “Q” will be used as it is the convention used to prefix a unique id as stated here: https://xapian.org/docs/omega/termprefixes.html)

We will use the id_Number column of the CSV as the idColumn in this example.

**indexFields:** This argument requires a list of 'list of fields' that will be indexed using a Xapian::TermGenerator.
Rxapian supports name indexing as well as numeric indexing.

Numeric Indexing:

:: 

  list( index=2 , prefix= “S”)
  list( index=8 , prefix= “XD”)

Here the first element specifies the index of the column that requires to be indexed with the prefix “S”.

Name indexing:

::

  list( name= “title” , prefix = “S”)
  list( name= “description” , prefix = “XD”)

Here the first element specifies the name of the column that requires to be indexed with the prefix “S”.

**stemmer:** This arguments requires the stemmer that should be applied to the Xapian::TermGenerator. It could be either the name or the two letter ISO639 code.

Parameters **filteFields** and **valueSlots** are not required for this example.

::

  db ← c(“path/to/database”)
  df ← read.csv(“path/to/csv”)
  id ← c(0)
  indexFields<-list(list(index=2,prefix="S"),list(index=8,prefix="XD"))
  stem ← c(“en”)

  xapian_index(dbpath = db, dataFrame = df, idColumn = id, indexFields = indexFields, stemmer = stem)

Searching
##########

xapian_search function takes the following format:

**xapian_search(dbpath , queryList , enquireList )**

**database:** This argument requires a list of paths to Xapian::Databases that should be queried.

Parameter **enquireList** is not required for this example.

**queryList:** For simple searching with Xapian::QueryParser, this argument should take the following format.

::

  query<-list(queryString="watch",
              prefix.fields=list(list(prefix="S",name="title"),
                                 list(prefix="XD",name="description")),
              stemmer="en")

::


  xapian_search(db, query)

