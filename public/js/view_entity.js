let viewConfig = {};

function setUpView(maps)
{
    viewConfig.subEntityMaps = maps;
    console.log(viewConfig);
    $( document ).ready(function() {
        // Set up watch control
        let watchControl = $('#entity-watch-control');
        watchControl.click(handleWatchClick);
        updateWatchControl(watchControl);
        
        // Set up auto-edit controls
        setAutoEdit();
        
        // Fill sub-entity displays
        jqEntityInfo = $('#entity-info');
        viewConfig.entityType = jqEntityInfo.attr('data-entitytype');
        viewConfig.entityId = jqEntityInfo.attr('data-entityid');
        updateSubEntities(Object.keys(viewConfig.subEntityMaps));
    });
}

function updateSubEntities(subEntityNames)
{
    for(subEntity of subEntityNames)
    {
        let table = $(`table[data-subentity=${subEntity}]`);
        displayTableLoading(table);
    }
    $.ajax({
        url: '/ajax/get_entity.php',
        data: {
            type: viewConfig.entityType,
            id: viewConfig.entityId,
            appends: subEntityNames
        },
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            updateSubEntityDisplays(subEntityNames, rData);
        }
    });
}

function updateSubEntityDisplays(subEntityNames, data)
{
    for(subEntity of subEntityNames)
    {
        let table = $(`table[data-subentity=${subEntity}]`);
        fillTable(table, data[subEntity], viewConfig.subEntityMaps[subEntity]);
    }
}

function handleWatchClick(event)
{
    event.preventDefault();
    let doWatch = event.target.dataset.iswatched == 'false';
    console.log('iswatched: ' + event.target.dataset.iswatched + ' doWatch: ' + doWatch);
    $.ajax({
        url: '/ajax/watch_item.php',
        data: {
            id: viewConfig.entityId,
            watch: doWatch
        },
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            event.target.dataset.iswatched = doWatch;
            updateWatchControl($(event.target));
        }
    });
}

function updateWatchControl(control)
{
    control.text( control.attr('data-iswatched') == 'true' ? 'Unwatch' : 'Watch' );
}