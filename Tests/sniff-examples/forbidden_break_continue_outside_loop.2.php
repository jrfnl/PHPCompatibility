<?php

// Make sure the 'if' statement finder actually find the correct corresponding if.
/*foreach( $test as $t )
	if($t === true ) {
		if ($t === true )
			echo 'hi';
	}
	elseif($t === null ) break;
*/

foreach( $test as $t )
	if($t === true )
		try {
			if ($t === true )
				echo 'hi';
			else
				echo 'bye';
		} catch ( Exception $e ) {
        }
	elseif($t === null ) break;


