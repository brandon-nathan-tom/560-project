let subEntityMaps = {
    organizations: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', '/view_entity.php?type=organization&id=' + row.id)
                    .text(row.name);
        }
    },
    projects: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', '/view_entity.php?type=project&id=' + row.id)
                    .text(row.name);
        }
    }
};

setUpView(subEntityMaps);