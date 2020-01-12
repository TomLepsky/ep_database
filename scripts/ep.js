$(document).ready(function() {

    $('#tree')
	    .on('changed.jstree', function (e, data) {
		    var link, d;

		    link = data.instance.get_node(data.selected[0]).a_attr['href'];
		    //d = data.instance.get_node(data.selected[0]);

		    $('#node-info').load(link);
		})
	    .jstree({
			'core' : {
				'data' : {
					'url' : '/showtree/',
					
					'data' : function (node) {
							return { 'li_attr' : node.li_attr };
						}
				}
			},
            'plugins' : ["state"],
		});

	

});

function getFile2DName () {

	var file = document.getElementById ('inputfile2D').value;
	file = file.replace (/\\/g, '/').split ('/').pop ();
	document.getElementById ('file2D-name').innerHTML = 'Имя файла: ' + file;

}

function getFile3DName () {

	var file = document.getElementById ('inputfile3D').value;
	file = file.replace (/\\/g, '/').split ('/').pop ();
	document.getElementById ('file3D-name').innerHTML = 'Имя файла: ' + file;

}

function getClassifierNumber() {

	var value = $('#classifier').val(); 
	$.ajax({
	    url: "/classifiernumber/",
	    type: "POST",
	    data: {classifier:value},
	    success: function (response) {
	            $('#number').val(response);
	    }
	});
}
