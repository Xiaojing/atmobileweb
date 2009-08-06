<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */
//Commented by Xiaojing at 9:46am on 06/05/09
// define("SERVER", "ldap.mit.edu");
//Added by Xiaojing at 9:46am on 06/05/09 to connect to ND LDAP server
define("SERVER","ldaps://directory.nd.edu/");

function mit_search($search) {
  	//$results = do_query(standard_query($search));
	//$results = do_query($search);
	$results = standard_query($search);
	/* Remove the eamil search function because ND LDAP doesnt allow for email search
	if($email_query = email_query($search)) {
		$results = do_query($email_query, $results);
	}
	*/
	return order_results($results, $search);
}

function order_results($results, $search) {
    $low_priority = array();
    $high_priority = array();
    foreach($results as $result) {
        $item = make_person($result);
        if(has_priority($item, $search)) {
          $high_priority[] = $item;
        } else {
          $low_priority[] = $item;
        }  
    }

    usort($low_priority, "compare_people");
    return array_merge($high_priority, $low_priority);
}

function has_priority($item, $search) {
  $words = explode(' ', trim($search));
  if(count($words) == 1) {
    $word = strtolower($words[0]);
    $emails = $item['email'];
    foreach($emails as $email) {
      $email = strtolower($email);
      if( ($email == $word) || 
          (substr($email, 0, strlen($word)+1) == "$word@") ) {
            return True;
      }
    } 
  }
  return False;
}

function email_query($search) {
  $words = explode(' ', trim($search));  
  if(count($words) == 1) {
    $word = $words[0];
	$word .= '*';    
    if(strpos($words, '@') === False) {
      //turns blpatt into blpatt@*
      $word .= '@*';
    }    
    return new QueryElement('mail', $word);
  }
}
/*The following code was added by Xiaojing to do the netid LDAP search in the standard_query function because the return of the original standard_query function which is passed to do_query() can not call out() in the do_query(). */
function standard_query($search, $search_results=array()) {
  
  //remove commas and periods
  $search = str_replace(',', ' ', $search);
  $search = str_replace('.', '*', $search);

  $query = new JoinAndQuery();

  foreach(split(" ", $search) as $word) {
	

    if($word != "") {
      $token_query = new LdapOrFilter();

      if(strlen($word) == 1) {
        //handle the special case of first initials
        $query_word = "$word*";

        //check for first or middle initial
	$token_query->_OR("cn", "$word*");
	
        // an ugly hack which forces ldap not to ignore spaces
                
        for($cnt = 0; $cnt < 26; $cnt++) {
          $chr = chr(ord('a') + $cnt);
          $token_query->_OR("cn", "*$chr $word*");
        } 
        $token_query->_OR("cn", "*-$word*");

      } else {
		$query_word = "$word*";
      }

	 $token_query
        ->_OR('uid', $query_word)
		->_OR('cn', $query_word)
		->_OR('sn', $query_word)
        ->_OR('mail', $query_word);
      //remove all non-digits from phone number before 
      //attempting phone number searches
      $phone_word = '*' . preg_replace('/\D/', '', $word) . '*';
	  
      if(strlen($phone_word) >= 4) {
        $token_query
          ->_OR('telephonenumber', $phone_word);
      }
      $query->_AND($token_query);
    }
  }
    $ds = ldap_connect(SERVER) or ldap_die("cannot connect");

    //turn off php Warnings, during ldap search
    //since it complains about search that go over the limit of 100
    $error_reporting = ini_get('error_reporting');
    error_reporting($error_reporting & ~E_WARNING);

	$return = array("sn", "cn", "uid", "givenname", "mail", "ndaffiliation", "edupersonaffiliation", 
	           "nddepartment", "ndtoplevelprimarydepartment", "ndtitle", "telephonenumber", "postaladdress");
	$sr = ldap_search($ds, "o=University of Notre Dame,st=Indiana,c=US", $query->out(), $return)
         or ldap_die("could not search"); 
	error_reporting($error_reporting);

    $entries = ldap_get_entries($ds, $sr)
         or ldap_die("could not get entries");

    for ($i = 0; $i < $entries["count"]; $i++) {
         $entry = $entries[$i];
         //some ldap entries have no usefull information
         //we dont want to return those
         if(lkey($entry, "sn", True)) { 
           //if one person has multiple ldap records
           //this code attempts to combine the data in the records
           if($old = $search_results[id_key($entry)]) {
           } else {
             $old = array();
           }
           $search_results[id_key($entry)] = array_merge($old, $entry);
         }
    }

    return $search_results;  

}

/* The following original code was commented by Xiaojing at 10:34am on 07/02/09 
function standard_query($search, $search_results=array()) {
  
  //remove commas and periods
  $search = str_replace(',', ' ', $search);
  $search = str_replace('.', '*', $search);

  $query = new JoinAndQuery();

  foreach(split(" ", $search) as $word) {
	echo $word;
    if($word != "") {
      $token_query = new LdapOrFilter();
	echo strlen($word);
      if(strlen($word) == 1) {
        //handle the special case of first initials
        $query_word = "$word*";

        //check for first or middle initial
	$token_query->_OR("cn", "$word*");

        
        //an ugly hack which forces ldap not to ignore spaces
              
        for($cnt = 0; $cnt < 26; $cnt++) {
          $chr = chr(ord('a') + $cnt);
          $token_query->_OR("cn", "*$chr $word*");
        } 
        $token_query->_OR("cn", "*-$word*");

      } else {
        //$query_word = "*$word*";
		$query_word = "$word";
		echo $query_word;
      }

	 $token_query
        ->_OR('uid', $query_word)
        ->_OR('mail', $query_word);
      //remove all non-digits from phone number before 
      //attempting phone number searches
      //$phone_word = '*' . preg_replace('/\D/', '', $word) . '*';
	  $phone_word = preg_replace('/\D/', '', $word);
      if(strlen($phone_word) >= 5) {
        $token_query
          ->_OR('homephone', $phone_word)
          ->_OR('telephonenumber', $phone_word)
          ->_OR('facsimiletelephonenumber', $phone_word);
      } 
      $query->_AND($token_query);
    }
  }
	return $query;   

}
*/
function do_query($query, $search_results=array()) {
    $ds = ldap_connect(SERVER) or ldap_die("cannot connect");

	//echo $query->out();

    //turn off php Warnings, during ldap search
    //since it complains about search that go over the limit of 100
    $error_reporting = ini_get('error_reporting');
    error_reporting($error_reporting & ~E_WARNING);
    /** The following original code was commented by Xiaojing at 10:24AM on 06/05/09 
    $sr = ldap_search($ds, "dc=mit, dc=edu", $query->out())
         or ldap_die("could not search");
	*/
	
	$return = array("givenname", "sn", "cn", "mail", "ndaffiliation", "edupersonaffiliation", "nddepartment", "ndtoplevelprimarydepartment", "ndtitle", "telephonenumber", "postaladdress");
	$sr = ldap_search($ds, "o=University of Notre Dame,st=Indiana,c=US", $query->out(), $return)
         or ldap_die("could not search");
	error_reporting($error_reporting);

    $entries = ldap_get_entries($ds, $sr)
         or ldap_die("could not get entries");

    for ($i = 0; $i < $entries["count"]; $i++) {
         $entry = $entries[$i];		 	
         //some ldap entries have no usefull information
         //we dont want to return those
         if(lkey($entry, "sn", True)) { 
           //if one person has multiple ldap records
           //this code attempts to combine the data in the records
           if($old = $search_results[id_key($entry)]) {
           } else {
             $old = array();
           }
           $search_results[id_key($entry)] = array_merge($old, $entry);
         }
    }

    return $search_results;
}


/*********************************************
 *
 *  this function compares people by firstname then lastname
 *
 *********************************************/
function compare_people($person1, $person2) {
  if($person1['surname'] != $person2['surname']) {
    return ($person1['surname'] < $person2['surname']) ? -1 : 1;
  } elseif($person1['givenname'] == $person2['givenname']) {
    return 0;
  } else {
    return ($person1['givenname'] < $person2['givenname']) ? -1 : 1;
  }
}

    
function lookup_username($id) {
  if(strstr($id, '=')) {    
    //look up person by "dn" (distinct ldap name)
    $ds = ldap_connect(SERVER);
    $sr = ldap_read($ds, $id, "(objectclass=*)");
    $entries = ldap_get_entries($ds, $sr);
    return make_person($entries[0]);
  } else {
    $tmp = do_query(new QueryElement("uid", $id));	
    foreach($tmp as $key => $first) {
      return make_person($first);
    }
  }
}



function lkey($array, $key, $single=False) {
  if($single) {
    return $array[$key][0];
  } else {
    $result = $array[$key];
    if($result === NULL) {
      return array();
    }
    unset($result["count"]);
    return $result;
  }
}

function id_key($info) {
  if($username = lkey($info, "uid", True)) {
    return $username;
  } else {
    return $info["dn"];
  }
}
/* The following original code was commented by Xiaojing at 2:19pm on 07/01/09 to match the ldap field key to ND's.
function make_person($info) {
  $person = array(
     "surname"     => lkey($info, "sn"),
     "givenname"   => lkey($info, "givenname"),
     "fullname"    => lkey($info, "cn"),
     "title"       => lkey($info, "title"),
     "dept"        => lkey($info, "ou"),
     "affiliation" => lkey($info, "edupersonaffiliation"),
     "address"     => lkey($info, "street"),
     "homephone"   => lkey($info, "homephone"),
     "email"       => lkey($info, "mail"),
     "room"        => lkey($info, "roomnumber"),
     "id"          => id_key($info),
     "telephone"   => lkey($info, "telephonenumber"),
     "fax"         => lkey($info, "facsimiletelephonenumber"),
     "office"      => lkey($info, "physicaldeliveryofficename")
  );

  foreach($person["office"] as $office) {
    if(!in_array($office, $person["room"])) {
      $person["room"][] = $office;
    }
  }

  return $person;
}  
*/

/* The following code was added by Xiaojing at 2:21pm on 07/01/09 to match the ldap field key to ND's.*/
function make_person($info) {
  $person = array(
	 "uid"         => lkey($info, "uid"),
     "surname"     => lkey($info, "sn"),
     "givenname"   => lkey($info, "givenname"),
     "fullname"    => lkey($info, "cn"),
     "title"       => lkey($info, "ndtitle"),
     "dept"        => lkey($info, "nddepartment"),
     "affiliation" => lkey($info, "edupersonaffiliation"),
     "address"     => lkey($info, "postaladdress"),
     "homephone"   => lkey($info, "homephone"),
     "email"       => lkey($info, "mail"),
     "room"        => lkey($info, "roomnumber"),     
     "id"          => id_key($info),
     "telephone"   => lkey($info, "telephonenumber"),
	 "fax"         => lkey($info, "facsimiletelephonenumber"),
	 "office"      => lkey($info, "physicaldeliveryofficename")
  );
  /*Remove the '$' from the adress field*/
  foreach($person["address"] as &$address) {
	$address = str_replace('$', ', ', $address);
}
  /*Remove the 2nd and 3rd fullname of a person*/
  unset ($person["fullname"][1]);
  unset ($person["fullname"][2]);
  
  //echo "Search Result is <br>";

  foreach($person["office"] as $office) {
    if(!in_array($office, $person["room"])) {
      $person["room"][] = $office;
    }
  }
  return $person;
}


/**                                                  *
 *  a series of classes alowing for the construction *
 *  of ldap queries                                  *
 *                                                   */
abstract class LdapQuery {
    abstract public function out();

    public static function escape($str) {
        $specials = array("*", "+", "=" , ",");
        foreach($specials as $special) {
            $str = str_replace($special, "\\" . $special, $str);
        }
        return $str;
    }
}

class LdapQueryList extends LdapQuery {

    protected $symbol;
    protected $queries=array();

    public function out() {
        $out = '(' . $this->symbol;
        foreach($this->queries as $query) {
	    $out .= $query->out();
        }
        $out .= ')';
        return $out;
    }
}   
    
class QueryElementList extends LdapQueryList {
    public function __construct($cond_arr=array()) {
        foreach($cond_arr as $field => $value) {
	    $this->add($field, $value);
        }
    }

    public function add($field, $value) {
         $this->queries[] = new QueryElement($field, $value);
         return $this;
    }
}

class LdapAndFilter extends QueryElementList {
    protected $symbol = '&'; 

    public function _AND($field, $value) {
        return $this->add($field, $value);
    }
}

class LdapOrFilter extends QueryElementList {
    protected $symbol = '|'; 

    public function _OR($field, $value) {
        return $this->add($field, $value);
    }
}

class JoinQuery extends LdapQueryList {

  public function __construct() {
     $this->queries = func_get_args();
  }
}

class JoinAndQuery extends JoinQuery {
  protected $symbol = '&';

  public function _AND(LdapQuery $query) {
        $this->queries[] = $query;
        return $this;
  }
}

class JoinOrQuery extends JoinQuery {
  protected $symbol = '|';

  public function _OR(LdapQuery $query) {
        $this->queries[] = $query;
        return $this;
  }
}


class QueryElement extends LdapQuery {
  protected $field;
  protected $value;
    
  public function __construct($field, $value) {
    $this->field = $field;
   
    //convert all multiple wildcards to a single wildcard
    $this->value = preg_replace('/\*+/', '*', $value);
  }
  
  public function out() {
    return '(' . $this->field . '=' . $this->value . ')';
  }
}

class RawQuery extends LdapQuery {
  protected $raw_query;
  
  public function __construct($raw_query) {
    $this->raw_query = $raw_query;
  }

  public function out() {
    return $this->raw_query;
  }
}  

function ldap_die($message) {
  throw new DataServerException($message);
}

?>