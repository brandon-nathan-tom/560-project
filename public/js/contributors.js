$( document ).ready(function() {
    setAutoSearch($('#contributors-table'), updateContributorsHandler);
    doAutoSearch($('#contributors-table').attr('data-searchentity'), updateContributorsHandler);
});

function updateContributorsHandler(data)
{
    fillTable($('#contributors-table'), data, { name: makeContributorLink, email: makeEmailLink, watch: makeWatchControl });
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
            doAutoSearch($('#contributors-table').attr('data-searchentity'), updateContributorsHandler);
        }
    });
}

function makeEmailLink(row)
{
    return $('<a></a>')
            .attr('href', 'mailto:' + row.email)
            .text(row.email);
}

function makeContributorLink(row)
{
    return $('<a></a>')
            .attr('href', '/view_entity.php?type=contributor&id=' + row.id)
            .text(row.name);
}