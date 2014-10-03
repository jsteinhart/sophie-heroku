<?php
class Symbic_View_Helper_FormComboBox extends Symbic_View_Helper_FormInput
{
    public function formComboBox($name, $value = null, $attribs = null, $options = null)
	{
		// TODO: use options set predefined entries
		return parent::formInput($name, $value, $attribs);
    }
}

/*
Use jQuery/$("#...").select2() with it's createSearchChoice option:

$("#...").select2({
	createSearchChoice: function(term, data)
		{
			if ($(data).filter(
					function()
					{
						return this.text.localeCompare(term)===0;
					}
				).length===0)
		{
			return {id:term, text:term};
		}
	},
	multiple: false,
	data: [
	// --> INSERT PREDEFINED ENTRIES HERE <--
		{id: 0, text: 'XXX'},
		{id: 1, text: 'YYY'},
		{id: 2, text: 'ZZZ'}
			...
	]
}); */