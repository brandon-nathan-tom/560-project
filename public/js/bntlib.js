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
    let theadrow = table.children('thead').children('tr').first();
    let arrKeys = theadrow.children('th').map( (index, ele) => ele.dataset['sourcecol'] ).get();
    
    let tbody = table.children('tbody');
    tbody.empty();
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

function setAutoSearch(table, updateHandler)
{
    let searchEntity = table.attr('data-searchentity');
    let thead = table.children('thead');
    let arrSearchKeys = thead.children('tr').first().children('th').map(
        (index, ele) => ele.dataset['searchkey'] === undefined ? '' : ele.dataset['searchkey'] )
        .get();
        
    let tInputRow = $('<tr></tr>');
    for(searchKey of arrSearchKeys)
    {
        let td = $('<td></td>');
        if(searchKey !== '')
        {
            let input = $('<input />')
                        .attr('data-searchentity', searchEntity)
                        .attr('data-searchkey', searchKey)
                        .css('width', '100%')
                        .on('input', getAutoSearchChangeHandler(updateHandler));
            td.append(input);
        }
        tInputRow.append(td);
    }
    thead.append(tInputRow);
}

function getAutoSearchChangeHandler(updateHandler)
{
    return function (event) {
        // Only execute the search 300 ms after the last change -
        // this prevents a query from being fired on every keypress
        let timer = arguments.callee.timer;
        if(timer !== undefined)
        {
            clearTimeout(timer);
        }
        console.log('Setting timeout...');
        arguments.callee.timer = setTimeout(function () {
                doAutoSearch(event.target.dataset.searchentity, updateHandler)
            }, 300);
    };
}

function doAutoSearch(entity, updateHandler)
{
    let searchData = { type: entity };
    $(`input[data-searchentity="${entity}"]`).each((index, ele) => {
        if(ele.value !== '')
        {
            searchData[ele.dataset.searchkey] = ele.value
        }
    });
    console.log(searchData);
    $.ajax({
        url: '/ajax/search_entity.php',
        data: searchData,
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function(rData) {
            console.log(rData);
            updateHandler(rData);
        }
    });
}

function displayTableLoading(table)
{
    displayTableMessage(table, "Loading...");
}

function displayTableMessage(table, message)
{
    let theadrow = table.children('thead').children('tr').first();
    let colspan = theadrow.children('th').length;
    
    let tbody = table.children('tbody');
    tbody.empty();
    let msgTd = $('<td></td>')
                        .attr('colspan', colspan)
                        .addClass('loading-filler')
    if(typeof(message) === 'string')
    {
        msgTd.text(message);
    }
    else
    {
        msgTd.append(message);
    }
    tbody.append($('<tr></tr>').append(msgTd));
}

function setAutoEdit()
{
    $('.autoedit-control')
        .one('click', handleEditClick)
        .text('Edit');
}

function handleEditClick(event)
{
    event.preventDefault();
    let target = $(event.target);
    let editTarget = $('#' + event.target.dataset.edittarget);
    
    wrapContentsForEdit(editTarget);
    target.text('Save');
    target.one('click', handleSaveClick);
}

function handleSaveClick(event)
{
    event.preventDefault();
    let editTarget = $('#' + event.target.dataset.edittarget);
    let editData = {
        type: editTarget.attr('data-editentity'),
        id: editTarget.attr('data-editid')
    };
    
    $('.autoedit-modified').each((index, ele) => {
        let editProperty = $(ele).attr('data-editproperty');
        let editText = $(ele).children().first().val();
        editData[editProperty] = editText;
    });
    
    console.log(editData);
    
    $.ajax({
        method: 'POST',
        url: '/ajax/update_entity.php',
        data: editData,
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            window.location.reload();
        }
    });
}

function wrapContentsForEdit(ele)
{
    let editText = ele.text().trim();
    let containerWidth = ele.width();
    ele.empty();
    let editable = null;
    if(ele.attr('data-edittype') === 'textarea')
    {
        editable = $('<textarea></textarea>');
    }
    else
    {
        editable = $('<input />');
    }
    
    editable.val(editText);
    ele.addClass('autoedit-modified');
    ele.append(editable);
    editable.width(containerWidth);
}