$( document ).ready(function () {
    updateWatchTables();
});

keyMaps = {
    project: {
        watch: makeRemoveControl
    },
    organization: {
        watch: makeRemoveControl,
        homepage: makeHomepageLink
    },
    contributor: {
        watch: makeRemoveControl,
        email: makeEmailLink
    }
};

function updateWatchTables()
{
    $('.quick-watch').each(function () {
        displayTableLoading($(this));
    });
    
    $.ajax({
        url: '/ajax/all_watched.php',
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: fillWatchTables
    });
}

function fillWatchTables(wData)
{
    console.log(wData);
    $('.quick-watch').each(function () {
        let tableData = wData[this.dataset.watchentity];
        let keyMapWithName = keyMaps[this.dataset.watchentity];
        keyMapWithName.name = (row) => {
            return $('<a></a>')
                    .attr('href', `/view_entity.php?type=${encodeURIComponent(this.dataset.watchentity)}&id=${row.id}`)
                    .text(row.name);
        };
        if(tableData.length === 0)
        {
            displayTableMessage($(this), `You do not currently watch any.`);
        }
        else
        {
            fillTable($(this), tableData, keyMapWithName);
        }
    });
}

function makeRemoveControl(row)
{
    let link = $( '<a></a>' )
                .addClass('no-visit')
                .attr('href', '#')
                .click(removeWatch)
                .attr('data-wid', row.id)
                .text('Remove');
    return link;
}

function removeWatch(event)
{
    event.preventDefault();
    $.ajax({
        url: '/ajax/watch_item.php',
        data: {
            id: event.target.dataset.wid,
            watch: false
        },
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            updateWatchTables();
        }
    });
}

function makeEmailLink(row)
{
    return $('<a></a>')
            .attr('href', 'mailto:' + row.email)
            .text(row.email);
}

function makeHomepageLink(row)
{
    return $('<a></a>')
            .attr('href', row.homepage)
            .text(row.homepage);
}