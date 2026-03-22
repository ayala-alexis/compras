<?php
class division_by_zero extends Exception {
	public function __toString() {
		$msg = 'Division by Zero';
		return $msg;
	}
}
function divide($num, $den) {
	try {
		if ($den == 0) {
			throw new division_by_zero ( 'Error fatal: No se puede dividir por cero(0).', 10 );
		} else {
			return $num / $den;
		}
	} catch ( division_by_zero $e ) {
		echo $e;
	}
}
echo divide ( 5, 0 );
?>