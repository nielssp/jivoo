<?php
abstract class BaseModel extends BaseObject implements ISelectable {

  public abstract function commit();

  public abstract function delete();

}