<?PHP
  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.

  class myAtomParser
  {
    // keeps track of current and preceding elements
    var $tags = array();

    // array containing all feed data
    var $output = array();

    // return value for display functions
    var $retval = "";

    var $errorlevel = 0;

    var $encoding = array();

    // constructor for new object
    function myAtomParser($file)
    {
      $errorlevel = error_reporting();
      error_reporting($errorlevel & ~E_NOTICE);

      // instantiate xml-parser and assign event handlers
      $xml_parser = xml_parser_create("");
      xml_set_object($xml_parser, $this);
      xml_set_element_handler($xml_parser, "startElement", "endElement");
      xml_set_character_data_handler($xml_parser, "parseData");

      // open file for reading and send data to xml-parser
      $fp = @fopen($file, "r") or die("<b>myAtomParser:</b> Could not open $file for input");
      while($data = fread($fp, 4096)) {
        xml_parse($xml_parser, $data, feof($fp)) or die(
          sprintf("myAtomParser: Error <b>%s</b> at line <b>%d</b><br>",
          xml_error_string(xml_get_error_code($xml_parser)),
          xml_get_current_line_number($xml_parser))
        );
      }
      fclose($fp);

      // dismiss xml parser
      xml_parser_free($xml_parser);

      error_reporting($errorlevel);
    }

    function startElement($parser, $tagname, $attrs=array())
    {
      if($this->encoding) {
        // content is encoded - so keep elements intact
        $tmpdata = "<$tagname";
        if($attrs) foreach($attrs as $key => $val) $tmpdata .= " $key=\"$val\"";
        $tmpdata .= ">";
        $this->parseData($parser, $tmpdata);
      } else {
        if($attrs['HREF'] && $attrs['REL'] && $attrs['REL'] == 'alternate') {
          $this->startElement($parser, 'LINK', array());
          $this->parseData($parser, $attrs['HREF']);
          $this->endElement($parser, 'LINK');
        }
        if($attrs['TYPE']) $this->encoding[$tagname] = $attrs['TYPE'];

        // check if this element can contain others - list may be edited
        if(preg_match("/^(FEED|ENTRY)$/", $tagname)) {
          if($this->tags) {
            $depth = count($this->tags);
            list($parent, $num) = each($tmp = end($this->tags));
            if($parent) $this->tags[$depth-1][$parent][$tagname]++;
          }
          array_push($this->tags, array($tagname => array()));
        } else {
          // add tag to tags array
          array_push($this->tags, $tagname);
        }
      }
    }

    function endElement($parser, $tagname)
    {
      // remove tag from tags array
      if($this->encoding) {
        if(isset($this->encoding[$tagname])) {
          unset($this->encoding[$tagname]);
          array_pop($this->tags);
        } else {
          if(!preg_match("/(BR|IMG)/", $tagname)) $this->parseData($parser, "</$tagname>");
        }
      } else {
        array_pop($this->tags);
      }
    }

    function parseData($parser, $data)
    {
      // return if data contains no text
      if(!trim($data)) return;

      $evalcode = "\$this->output";
      foreach($this->tags as $tag) {
        if(is_array($tag)) {
          list($tagname, $indexes) = each($tag);
          $evalcode .= "[\"$tagname\"]";
          if(${$tagname}) $evalcode .= "[" . (${$tagname} - 1) . "]";
          if($indexes) extract($indexes);
        } else {
          if(preg_match("/^([A-Z]+):([A-Z]+)$/", $tag, $matches)) {
            $evalcode .= "[\"$matches[1]\"][\"$matches[2]\"]";
          } else {
            $evalcode .= "[\"$tag\"]";
          }
        }
      }

      if(isset($this->encoding['CONTENT']) && $this->encoding['CONTENT'] == "text/plain") {
        $data = "<pre>$data</pre>";
      }

      eval("$evalcode .= '" . addslashes($data) . "';");
    }

    // display a single feed as HTML
    function display_feed($data, $limit)
    {
      extract($data);
      if($TITLE) {
        // display feed information
        $this->retval .= "<h1>";
        if($LINK) $this->retval .= "<a href=\"$LINK\" target=\"_blank\">";
        $this->retval .= stripslashes($TITLE);
        if($LINK) $this->retval .= "</a>";
        $this->retval .= "</h1>\n";
        if($TAGLINE) $this->retval .= "<P>" . stripslashes($TAGLINE) . "</P>\n\n";
        $this->retval .= "<div class=\"divider\"><!-- --></div>\n\n";
      }
      if($ENTRY) {
        // display feed entry(s)
        foreach($ENTRY as $item) {
          $this->display_entry($item, "FEED");
          if(is_int($limit) && --$limit <= 0) break;
        }
      }
    }

    // display a single entry as HTML
    function display_entry($data, $parent)
    {
      extract($data);
      if(!$TITLE) return;

      $this->retval .=  "<p>";
      if($LINK) $this->retval .=  "<a href=\"$LINK\" target=\"_blank\">";
      $this->retval .= stripslashes($TITLE);
      if($LINK) $this->retval .= "</a>";
      $this->retval .=  "";
      if($ISSUED) $this->retval .= " <small>($ISSUED)</small>";
      $this->retval .=  "</p>\n";

      if($AUTHOR) {
        //$this->retval .=  "<P><b>Author:</b> " . stripslashes($AUTHOR['NAME']) . "</P>\n\n";
      }
      if($CONTENT) {
        $this->retval .=  "<P>" . stripslashes($CONTENT) . "</P>\n\n";
      } elseif($SUMMARY) {
        $this->retval .=  "<P>" . stripslashes($SUMMARY) . "</P>\n\n";
      }
    }

    function fixEncoding(&$input, $key, $output_encoding)
    {
      if(!function_exists('mb_detect_encoding')) return $input;

      $encoding = mb_detect_encoding($input);
      switch($encoding)
      {
        case 'ASCII':
        case $output_encoding:
          break;
        case '':
          $input = mb_convert_encoding($input, $output_encoding);
          break;
        default:
          $input = mb_convert_encoding($input, $output_encoding, $encoding);
      }
    }

    // display entire feed as HTML
    function getOutput($limit=false, $output_encoding='UTF-8')
    {
      $this->retval = "";
      $start_tag = key($this->output);

      switch($start_tag)
      {
        case "FEED":
          foreach($this->output as $feed) $this->display_feed($feed, $limit);
          break;
        default:
          die("Error: unrecognized start tag '$start_tag' in getOutput()");
      }

      if($this->retval && is_array($this->retval)) {
        array_walk_recursive($this->retval, 'myAtomParser::fixEncoding', $output_encoding);
      }
      return $this->retval;
    }

    // return raw data as array
    function getRawOutput($output_encoding='UTF-8')
    {
      array_walk_recursive($this->output, 'myAtomParser::fixEncoding', $output_encoding);
      return $this->output;
    }
  }
?>
