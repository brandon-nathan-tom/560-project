$( document ).ready(function() {
    setAutoSearch($('#orgs-table'), updateOrgsHandler);
    doAutoSearch($('#orgs-table').attr('data-searchentity'), updateOrgsHandler);
});

function updateOrgsHandler(data)
{
    fillTable($('#orgs-table'), data, { name: makeOrganizationLink, email: makeEmailLink, watch: makeWatchControl });
}

function makeOrganizationLink(row)
{
    return $('<a></a>')
            .attr('href', '/view_entity.php?type=organization&id=' + row.id)
            .text(row.name);
}

function makeEmailLink(row)
{
    return $('<a></a>')
            .attr('href', 'mailto:' + row.email)
            .text(row.email);
}

function makeWatchControl(row)
{
    let link = $( '<a></a>' )
                .addClass('no-visit')
                .attr('href', '#')
                .click(handleWatchClick)
                .attr('data-orgid', row.id)
                .attr('data-iswatched', row.watched)
                .text(row.watched ? "Unwatch" : "Watch");
    return link;
}

function handleWatchClick(event)
{
    event.preventDefault();
    $.ajax({
        url: '/ajax/watch_item.php',
        data: {
            id: event.target.dataset.orgid,
            watch: event.target.dataset.iswatched == 'false'
        },
        error: function (rData) {
            alert("Database error (see log for details)");
            console.log(rData.responseText);
        },
        success: function (rData) {
            console.log(rData);
            doAutoSearch($('#orgs-table').attr('data-searchentity'), updateOrgsHandler);
        }
    });
}