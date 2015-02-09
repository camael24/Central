<?php

require_once __DIR__.
	 DIRECTORY_SEPARATOR.'..'.
	 DIRECTORY_SEPARATOR.'..'.
	 DIRECTORY_SEPARATOR.'Hoa'.
	 DIRECTORY_SEPARATOR.'Core'.
	 DIRECTORY_SEPARATOR.'Core.php';

$stub    = __DIR__.DIRECTORY_SEPARATOR.'stub.php';
$hoaPath = realpath(__DIR__ . DIRECTORY_SEPARATOR.'..' . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Hoa');


$finder = new \Hoa\File\Finder();
$finder
    ->in($hoaPath)
    ->name('#\.php$#')
    ->maxDepth(100)
    ->files();

$f = array();
// READ
foreach ($finder as $key => $value) {
    $pathname = $value->getPathName();
    $name     = $value->getBasename();

    preg_match('#(Hoa[^\.]*)\.php$#', $pathname, $m);

    if(isset($m[1]) === true)
    {
		$name = $m[1];
		$end = '';
		if (preg_match('#Core#', $pathname)) {
		     $raw = file_get_contents($pathname);

		     preg_match('#\nclass_alias\(([^,]*),(.*)\)#', $raw, $o);

		    if (isset($o[1]) and $o[1] !== '') {
		        $name  = substr(trim($o[1]), 1, -1);
		        $end   = substr(trim($o[2]), 1, -1);
		    }

		} else {
		    $raw = file_get_contents($pathname);
		       preg_match('#flexEntity\(\'(.*)\'#', $raw, $o);

		    if (count($o) > 0) {
		        $c   = $o[1];
		        $end = \Hoa\Core\Consistency\Consistency::getEntityShortestName($c);
		    }
		}

		if ($end !== '') {
		    $f[] = array('class' => str_replace('/', '\\', $name), 'alias' => $end);
		}
	}
}

// WRITE
$out = '<?php '."\n";
foreach ($f as $class) {
    $ns = substr($class['alias'], 0, strrpos($class['alias'], '\\'));
    $c  = substr($class['alias'], strrpos($class['alias'], '\\')+1);

    $out .= 'namespace '.$ns.' {'."\n";
    $out .= 'class '.$c.' extends \\'.$class['class'].' {}'."\n";
    $out .= '}'."\n";

}

file_put_contents($stub, $out);
