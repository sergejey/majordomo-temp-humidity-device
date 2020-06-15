<?php

$ot=$this->object_title;
$description = $this->description;
if (!$description) {
    $description = $ot;
}
$linked_room=$this->getProperty('linkedRoom');

$value=(float)$this->getProperty('valueHumidity');
$minValue=(float)$this->getProperty('minHumidityValue');
$maxValue=(float)$this->getProperty('maxHumidityValue');
$is_normal=(int)$this->getProperty('normalHumidityValue');
$directionTimeout=(int)$this->getProperty('directionTimeout');
if (!$directionTimeout) {
    $directionTimeout=1*60*60;
}

if ($maxValue==0 && $minValue==0 && !$is_normal) {
    $this->setProperty('normalHumidityValue', 1);
} elseif (($value>$maxValue || $value<$minValue) && $is_normal) {
    $this->setProperty('normalHumidityValue', 0);
    if ($this->getProperty('notify')) {
        //out of range notify
        say(LANG_DEVICES_NOTIFY_OUTOFRANGE. ' ('.$description.' '.$value.')', 2);
    }
} elseif (($value<=$maxValue && $value>=$minValue) && !$is_normal) {
    $this->setProperty('normalHumidityValue', 1);
    if ($this->getProperty('notify')) {
        //back to normal notify
        say(LANG_DEVICES_NOTIFY_BACKTONORMAL. ' ('.$description.' '.$value.')', 2);
    }
}


$data1 = getHistoryValue($this->object_title.".valueHumidity", time()-$directionTimeout);
$direction = 0;
if ($data1>$value) {
    $direction=-1;
} elseif ($data1<$value) {
    $direction=1;
}
$currentDirection = $this->getProperty('directionHumidity');
if ($currentDirection != $direction) {
    $this->setProperty('directionHumidity',$direction);
}

if ($linked_room && $this->getProperty('mainSensor')) {
        sg($linked_room.'.temperature',$this->getProperty('value'));
        sg($linked_room.'.humidity',$this->getProperty('valueHumidity'));
}

$this->callMethodSafe('keepAlive');
$this->callMethod('statusUpdated');