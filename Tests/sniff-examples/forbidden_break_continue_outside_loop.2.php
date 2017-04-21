<?php

if ( $a === $b ) {
    continue;
} elseif ( $a === $c ) {
    continue;
} else {
    break;
}

function testFunctionA() {
    continue;
}

function testFunctionB() {
    break;
}

continue;


foreach( $a as $b )
	if ($a === $b)
		foreach($b as $c) {
			echo 'something';
		}
	else
		break;


while ($whileExample < 10) {
	$a = function() {
	    if ($whileExample === 5) {
	        continue; // This should throw an error as the closure is a fixed scope in which we find no loop structure.
	    }
	}
    if ($a === 8) {
        break;
    }
    $whileExample++;
}
