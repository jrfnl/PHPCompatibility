<?php

/*
 * Issue #292: Prevent false positives for non-scoped control structures.
 */
while ($whileExample < 10) {
	$a = function() {
	    for( $i = 0; $i < 1; $i++)
			// This should *not* throw an error as the closure contains a non-scoped loop structure.
	        continue;
	}
}

// Verify that walking up a try/catch statement works correctly.
while ($whileExample < 10)
	try {
		something();
	} catch( ArithmicException $e ) {
		try {
			// Try something else.
		} catch ( OtherException $e ) {
			continue;
		}
	} catch( Exception $e ) {
		// Do something else.
		continue;
	} finally {
		break;
	}

// Make sure the 'if' statement finder actually find the correct corresponding if.
foreach( $test as $t )
	if($t === true ) {
		if ($t === true )
			echo 'hi';
	}
	elseif($t === null ) break;


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


$is_advanced_search_panel = false;
foreach ($w2dc_instance->search_fields->search_fields_array AS $search_field)
	if ($search_field->content_field->advanced_search_form) {
		$is_advanced_search_panel = true;
		break;
	}
		
foreach( $test as $t )
	if($t === true ) break;
	elseif($t === null ) break;
	else if($t === 123 ) break;
	else if($t === '123' ) break;
	else continue;

foreach($stuff as $num)
	if ($num %2 ) {
		continue;
	} else {
		break;
	}

foreach ($sql as $s)
	if (!$this->execute) echo "<pre>",$s.";\n</pre>";
	else {
		$ok = $this->connDest->Execute($s);
		if (!$ok)
			if ($this->neverAbort) continue;
			else break;
	}

for ($i = 0; $i < 10; $i++)
    if ($i === 5)
        continue;

    else if ($i === 8)
        break;

foreach ($forExample as $key => $value)
    if ($key === 5)
        continue;

    elseif ($key === 8)
        break;

foreach ($forExample as $key => $value)
    if ($key === 5)
        continue;

    else if($key === 8)
    	echo 'something';
        break; // This one *should* throw an error as it is not the first statement, therefore not part of the else nor the foreach.


$whileExample = 10;
while ($whileExample < 10)
    if ($whileExample === 5) {
        $whileExample++;
        continue;
    } elseif ($whileExample === 8) {
        break;
    }

function testingScope() {
    for ($i = 0; $i < 10; $i++)
        if ($i === 5)
            continue;

        elseif ($i === 8)
            break;
}

// Nested mixed scoped and non-scoped conditions.
for ($i = 0; $i < 10; $i++)
    if ($i < 6)
	    if ($i < 5) {
		    if ($i < 4)
			    if ($i < 3)
				    if ($i === 2)
				        continue;
		}
		else if ($i !== 7)
			exit;
		else if ($i !== 8) {
			if ($i === 9)
				break;
		}
		else
			if ($i = 0)
				continue;

// Nested scope conditions.
for ($i = 0; $i < 10; $i++)
    if ($i < 6) {
	    if ($i < 5) {
		    if ($i < 4) {
			    if ($i < 3) {
				    if ($i === 2) {
				        continue;
					}
				}
			}
		}
	}


// Make sure that the non-scoped control structure finder does not get confused with scoped/non-scoped.
while ($i < 10 ) $i++;
break; // This one *should* throw an error as it is not the first statement, therefore not part of the while.

while ($i < 10 ) { 
	$i++;
}
continue; // This one *should* throw an error as it is not the first statement, therefore not part of the while.

