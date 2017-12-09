/**
 * Fills a table with the given data.
 * @param table     jQuery object representing the table to fill.
 * @param arrData   Associative array containing the data with which to fill the table
 *                  (indexed by column key given in the corresponding <th> element's
 *                  data-sourcecol attribute.)
 * @param keyMap    Optional. Maps column keys to functions of the data row. If a column
 *                  key appears in this array, the function will be called to generate the
 *                  column contents. Otherwise, the column key will be looked up in the
 *                  data row directly and the contents inserted as text.
 *                  Callbacks should return a string (which will be inserted as text, not HTML)
 *                  or a jQuery object, which will be appended to the table cell.
 */
function fillTable(table, arrData, keyMap) {
    let thead = table.children('thead');
    let arrKeys = thead.find('th').map( (index, ele) => ele.dataset['sourcecol'] ).get();
    
    let tbody = table.children('tbody');
    for(rowData of arrData)
    {
        let tr = $( '<tr></tr>' );
        for(colKey of arrKeys)
        {
            let td = $( '<td></td>' );
            let generateTdContents = keyMap[colKey];
            if(generateTdContents !== undefined)
            {
                let tdContents = generateTdContents(rowData);
                if(typeof(tdContents) === 'string')
                {
                    td.text(tdContents);
                }
                else
                {
                    td.append(tdContents);
                }
            }
            else
            {
                td.text(rowData[colKey]);
            }
            tr.append(td);
        }
        tbody.append(tr);
    }
}