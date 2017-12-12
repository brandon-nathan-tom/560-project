let subEntityMaps = {
    dl_mirrors: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', row.uri)
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
    contributors: {
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
    },
    licenses: {
        name: function (row) {
            return $('<a></a>')
                    .attr('href', row.uri)
                    .text(row.name);
        }
    }
};

setUpView(subEntityMaps);