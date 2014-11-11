<?php
require_once("xapian.php");
require_once("parsecsv.php");
require_once("logger.php");

function search($dbpath, $querystring, $offset = 0, $pagesize = 10)
{
    // offset - defines starting point within result set
    // pagesize - defines number of records to retrieve

    // Open the database we're going to search.
    $db = new XapianDatabase($dbpath);

### Start of example code.
    // Set up a QueryParser with a stemmer and suitable prefixes
    $queryparser = new XapianQueryParser();
    $queryparser->set_stemmer(new XapianStem("en"));
    $queryparser->set_stemming_strategy(XapianQueryParser::STEM_SOME);
    $queryparser->add_prefix("title", "S");
    $queryparser->add_prefix("description", "XD");
    $queryparser->add_boolean_prefix("material", "XM");

    // And parse the query
    $query = $queryparser->parse_query($querystring);
### End of example code.

    // Use an Enquire object on the database to run the query
    $enquire = new XapianEnquire($db);
    $enquire->set_query($query);

    // Set up a spy to inspect the MAKER value at slot 1
    $spy = new XapianValueCountMatchSpy(1);
    $enquire->add_matchspy($spy);

    // Retrieve the matches and compute start and end points
    $matches = $enquire->get_mset($offset, $pagesize);
    $start = $matches->begin();
    $end = $matches->end();

    // Use an array to record the DocIds of each match
    $docids = array();

    while (!($start->equals($end)))
    {
        // retrieve the document and its data
        $doc = $start->get_document();
        $fields = json_decode($doc->get_data());
        $position = $start->get_rank() + 1;

        // record the docid
        $docid = $start->get_docid();
        $docids[] = $docid;

        // display the results
        printf("%d: #%03d %s\n", $position, $docid, $fields->TITLE);

        // increment MSet iterator and our counter
        $start->next();
    }

    // Parse and display the spy values
    $spy_start = $spy->values_begin();
    $spy_end = $spy->values_end();

    while (!($spy_start->equals($spy_end)))
    {
        print sprintf("Facet: %s; count: %d\n",
            $spy_start->get_term(),
            $spy_start->get_termfreq()
        );

        $spy_start->next();
    }

    // Finally, make sure we log the query and displayed results
    log_info(sprintf("xapian.search:'%s'[%d:%d] = %s",
                $querystring,
                $offset,
                $offset+$pagesize,
                implode(" ", $docids)
            ));
}

if ($argc < 2) {
    print "Usage: php $argv[0] DBPATH QUERYTERM...\n";
    die();
}

search($argv[1], join(' ', array_slice($argv, 2)));
?>
