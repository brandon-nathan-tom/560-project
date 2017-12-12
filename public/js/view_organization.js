let subEntityMaps = {
    projects: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', '/view_entity.php?type=project&id=' + row.id)
                    .text(row.name);
        }
    },
    websites: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', row.uri)
                    .text(row.name);
        }
    },
    members: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', '/view_entity.php?type=contributor&id=' + row.id)
                    .text(row.name);
        },
        email: function (row) {
            return $('<a></a>')
                    .attr('href', 'mailto:' + row.email)
                    .text(row.email);
        }
    }
};

setUpView(subEntityMaps);