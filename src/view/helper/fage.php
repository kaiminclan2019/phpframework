<?php
class view_helper_fage
{
	private $data = '';
	public function __construct()
	{
	}
	public function init($date)
	{
		$this->data = $date;
		return $this;
	}
	
	public function get()
	{
		$birthday = $this->data;
		if(strpos($birthday,'-') === false){
			$birthday = date('Y-m-d',$birthday);
		}
		$ageData = $this->calAge($birthday);
		if($ageData['year']){
		}
		return $ageData['year'];
	}
	/**
   * 计算年龄精准到年月日
   * @param type $birthday
   * @return array
   */
   
  public function calAge($birthday) {
    list($byear, $bmonth, $bday) = explode('-', $birthday);
    list($year, $month, $day) = explode('-', date('Y-m-d'));
    $bmonth = intval($bmonth);
    $bday = intval($bday);
    if ($bmonth < 10) {
      $bmonth = '0' . $bmonth;
    }
    if ($bday < 10) {
      $bday = '0' . $bday;
    }
    $bi = intval($byear . $bmonth . $bday);
    $ni = intval($year . $month . $day);
    $not_birth = 0;
    if ($bi > $ni) {
      $not_birth = 1;
      $tmp = array($byear, $bmonth, $bday);
      list($byear, $bmonth, $bday) = array($year, $month, $day);
      list($year, $month, $day) = $tmp;
      list($bi, $ni) = array($ni, $bi);
    }
    $years = 0;
    while (($bi + 10000) <= $ni) {//先取岁数
      $bi += 10000;
      $years++;
      $byear++;
    }//得到岁数后 抛弃年
    list($m, $d) = $this->getMD(array($year, $month, $day), array($byear, $bmonth, $bday));
    return array('year' => $years, 'month' => $m, 'day' => $d, 'not_birth' => $not_birth);
  }
   
  /**
   * 只能用于一年内计算
   * @param type $ymd
   * @param type $bymd
   */
  public function getMD($ymd, $bymd) {
    list($y, $m, $d) = $ymd;
    list($by, $bm, $bd) = $bymd;
    if (($m . $d) < ($bm . $bd)) {
      $m +=12;
    }
    $month = 0;
    while ((($bm . $bd) + 100) <= ($m . $d)) {
      $bm++;
      $month++;
    }
    if ($bd <= $d) {//同处一个月
      $day = $d - $bd;
    } else {//少一个月
      $mdays = $bm > 12 ? $this->_getMothDay( ++$by, $bm - 12) : $this->_getMothDay($by, $bm);
      $day = $mdays - $bd + $d;
    }
    return array($month, $day);
  }
   
  private function _getMothDay($year, $month) {
    switch ($month) {
      case 1:
      case 3:
      case 5:
      case 7:
      case 8:
      case 10:
      case 12:
        $day = 31;
        break;
      case 2:
        $day = (intval($year % 4) ? 28 : 29); //能被4除尽的为29天其他28天
        break;
      default:
        $day = 30;
        break;
    }
    return $day;
  }
}