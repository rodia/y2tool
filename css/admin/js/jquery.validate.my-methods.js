/**
 * It extends jQuery Validation plugin to enhance capabilities to validate array of inputs
 *
 * @author		Andrea Venturi
 * @copyright	Copyright (c) 2011 Andrea Venturi
 * @license		http://www.gnu.org/licenses/lgpl.html	GNU Lesser General Public License
 * @version		1.0
 *
 */

jQuery.extend(jQuery.validator.prototype, {

	/*
	 * Modifica necessaria per controllare tutti i campi nel caso in cui ci sia un array di <input>
	 * I campi interessati devono avere l'attributo id valorizzato
	*/
	checkForm: function() {
		this.prepareForm();
		for ( var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++ ) {
			if (this.findByName( elements[i].name ).length != undefined && this.findByName( elements[i].name ).length > 1) {
				for (var cnt = 0; cnt < this.findByName( elements[i].name ).length; cnt++) {
					this.check( this.findByName( elements[i].name )[cnt] );
				}
			} else {
				this.check( elements[i] );
			}
		}
		return this.valid();
	},
		
	showErrors: function(errors) {
		if(errors) {
			// add items to error list and map
			$.extend( this.errorMap, errors );
			this.errorList = [];
			for ( var name in errors ) {
				this.errorList.push({
					message: errors[name],
					/* NOTE THAT I'M COMMENTING THIS OUT
					element: this.findByName(name)[0]
					*/
					element: this.findById(name)[0]
				});
			}
			// remove items from success list
			this.successList = $.grep( this.successList, function(element) {
				return !(element.name in errors);
			});
		}
		this.settings.showErrors
			? this.settings.showErrors.call( this, this.errorMap, this.errorList )
			: this.defaultShowErrors();
	},

	findById: function( id ) {
		// select by name and filter by form for performance over form.find("[id=...]")
		var form = this.currentForm;
		return $(document.getElementById(id)).map(function(index, element) {
			return element.form == form && element.id == id && element || null;
		});
	}

});
/******************************************************************************/
