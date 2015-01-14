<?php
class ArchiveWidget extends Widget {
  
  protected $models = array('Post');
  
  protected $helpers = array('Html');
  
  public function getDefaultTitle() {
    return tr('Archive');
  }
  
  public function main($config) {
    // TODO SQLite support for this widget? (YEAR and MONTH not supported)
    $selection = $this->Post
      ->where('status = %PostStatus', 'published')
      ->groupBy('YEAR(published)')
      ->orderByDescending('YEAR(published)')
      ->select(array(
        'YEAR(published)' => 'year',
        'COUNT(*)' => 'num'
      ));
    $years = array();
    $selectedYear = date('Y');
    foreach ($selection as $year) {
      if ($year['year'] == $selectedYear) {
        $months = $this->Post
          ->where('status = %PostStatus', 'published')
          ->and('YEAR(published) = %i', $selectedYear)
          ->groupBy('MONTH(published)')
          ->orderByDescending('MONTH(published)')
          ->select(array(
            'MONTH(published)' => 'month',
            'COUNT(*)' => 'num'
          ));
        $year['months'] = array();
        foreach ($months as $month) {
          $month['monthName'] = tdate('F', strtotime($selectedYear . '-' . $month['month'] . '-01'));
          $year['months'][] = $month;
        }
      }
      $years[] = $year;
    }
    $this->years = $years;
    return $this->fetch();
  }
}
