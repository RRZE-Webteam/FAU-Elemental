document.addEventListener( 'DOMContentLoaded', function () {
	document
		.getElementById( 'category-sort' )
		.addEventListener( 'change', function () {
			const [ orderby, order ] = this.value.split( '-' );
			this.form.elements.order.value = order;
			this.form.elements.orderby.value = orderby;
			this.form.submit();
		} );
} );
