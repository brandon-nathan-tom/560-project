$( document ).ready(function () {
	let tableData = [
		{ col1: 'C1Text', col2: 'C2Text' },
		{ col1: 'C1Text2', col2: 'C2Text2' },
		{ col1: 'C1Text3', col2: 'C2Text3' }
	];
		
	let keyMaps = {
		cb: (rowData) => (rowData.col1 + rowData.col2)
	};
	
	fillTable($('#test-table'), tableData, keyMaps);
	
});