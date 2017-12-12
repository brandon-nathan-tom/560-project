$( document ).ready(function() {
    setAutoSearch($('#projects-table'), updateProjectsHandler);
    doAutoSearch($('#projects-table').attr('data-searchentity'), updateProjectsHandler);
});

function updateProjectsHandler(data)
{
    fillTable($('#projects-table'), data, { name: makeProjectLink, owner: makeOwnerLink, watch: makeWatchControl });
}

function makeWatchControl(row)
{
    let link = $( '<a></a>' )
                .addClass('no-visit')
                .attr('href', '#')
                .click(handleWatchClick)
                .attr('data-projectid', row.id)
                .attr('data-iswatched', row.watched)
                .text(row.watched == true ? "Unwatch" : "Watch");
    return link;
}

function handleWatchClick(event)
{
    event.preventDefault();
    $.ajax({
        url: '/ajax/watch_item.php',
        data: {
            id: event.target.dataset.projectid,
            watch: event.target.dataset.iswatched == 'false'
        },
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            doAutoSearch($('#projects-table').attr('data-searchentity'), updateProjectsHandler);
        }
    });
}

function makeOwnerLink(row)
{
    return $('<a></a>')
            .attr('href', '/view_entity.php?type=organization&id=' + row.owner_id)
            .text(row.owner);
}

function makeProjectLink(row)
{
    return $('<a></a>')
            .attr('href', '/view_entity.php?type=project&id=' + row.id)
            .text(row.name);
}