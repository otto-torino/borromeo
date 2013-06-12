var borromeo = {};

borromeo.toggleIndex = function() {
  var index = document.id('borromeo-doc-index');

  var myfx = new Fx.Tween(index, {duration: 'short'});

  if(index.retrieve('collapsed')) {
    myfx.start('width', index.retrieve('width')).chain(function() {
      index.setStyles({
        'padding': '10px',
        'border-width': '1px',
        'background': '#fff'
      });
      myfx.start('opacity', 1);
      index.setStyle('max-height', '');
      index.store('collapsed', false);
    });
  }
  else {
    var index_coords = index.getCoordinates();
    index.store('width', index_coords.width);
    index.setStyle('max-height', index_coords.height + 'px');
    myfx.start('opacity', 0).chain(function() {
      myfx.start('width', 0);
      index.setStyles({
        'padding': 0,
        'border-width': 0,
        'background': 'transparent'
      });
      index.store('collapsed', true);
    })
  }
}

borromeo.toggleNotes = function() {
  var notes = document.id('borromeo-doc-annotations');

  var myfx = new Fx.Tween(notes, {duration: 'short'});

  if(notes.retrieve('collapsed')) {
    myfx.start('width', notes.retrieve('width')).chain(function() {
      notes.setStyles({
        'padding': '10px',
        'border-width': '1px',
        'background': '#fff'
      });
      myfx.start('opacity', 1);
      notes.store('collapsed', false);
    });
  }
  else {
    notes.store('width', notes.getCoordinates().width);
    myfx.start('opacity', 0).chain(function() {
      myfx.start('width', 0);
      notes.setStyles({
        'padding': 0,
        'border-width': 0,
        'background': 'transparent'
      });
      notes.store('collapsed', true);
    })
  }
}

borromeo.panelsScrolling = function() {

  ['borromeo-doc-index', 'borromeo-doc-annotations'].each(function(p) {
    if(typeOf($(p)) != 'null') {
    
      var pan = $(p);
      console.log(pan);
      var pan_coord = pan.getCoordinates();
      var vp = getViewport();

      if(pan_coord.top + pan_coord.height > vp.height) {

        pan.setStyles({
          'height': (vp.height - pan_coord.top - 20) + 'px',
          'overflow': 'hidden'
        });

        var myscrollable = new Scrollable(pan);

      }
    }
  })
}

window.addEvent('load', borromeo.panelsScrolling);
