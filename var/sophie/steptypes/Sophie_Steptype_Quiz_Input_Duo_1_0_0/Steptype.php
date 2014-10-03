<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Quiz_Input_1_0_0');

class Sophie_Steptype_Quiz_Input_Duo_1_0_0_Steptype extends Sophie_Steptype_Quiz_Input_1_0_0_Steptype
{
	public $questionNumber = 2;
}