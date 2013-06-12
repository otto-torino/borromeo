var mootab = new Class({

  options: {
    init_index: 0
  },
  Implements: [Options],
  initialize: function(container, tab_selector, title_selector, options) {
  
    this.setOptions(options);

    this.container = $(container);
    this.container.addClass('mootab');
    
    // hide tabs construction
    this.container.store('display', this.container.style.display);
    this.container.style.display = 'none';

    var tabs = [];
    var titles = [];
    this.tab_titles = [];
    this.tab_contents = [];

    this.container.getChildren(tab_selector).each(function(tab) {
      tabs.push(tab);
      titles.push(tab.getElements(title_selector)[0]);
    })

    // once contents have been saved, delete them
    this.container.empty();

    // render the structure
    this.render(titles, tabs);

    // show the first tab
    this.show(this.options.init_index);

    // show tabs
    this.container.style.display = this.container.retrieve('display');

  },
  render: function(titles, tabs) {

    // titles bar
    var titles_bar = new Element('div.tab_titles');
    titles.each(function(title, index) {
      var tab_title = new Element('span.link').set('html', title.get('html'))
        .addEvent('click', function() {
          this.show(index);
        }.bind(this))
        .inject(titles_bar);
      this.tab_titles.push(tab_title);
    }.bind(this))

    titles_bar.inject(this.container);

    // contents
    this.content_container = new Element('div.tab_contents');
    tabs.each(function(tab) {
      tab.addClass('hidden');
      tab.inject(this.content_container);
      this.tab_contents.push(tab);
    }.bind(this));

    this.content_container.inject(this.container);

  },
  show: function(index) {

    if(typeOf(this.active_index) != 'null') {
      this.tab_titles[this.active_index].removeClass('selected');
      this.tab_contents[this.active_index].addClass('hidden');
    }
    this.tab_titles[index].addClass('selected');
    this.tab_contents[index].removeClass('hidden');

    this.active_index = index;

  }

})
