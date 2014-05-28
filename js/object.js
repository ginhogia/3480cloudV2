// Data objects

function Session(data)
{
  var me = this;
  me.user = new User(data.user);
  me.club = new Club(data.club);
  me.page = new Page(data.page);
}

function Club(data)
{
  var me = this;
  me.id = data.id;
  me.name = data.name;
  me.member = [];
  for (var i = 0; i < data.member.length; ++i)
  {
    me.member.push(new User(data.member[i]));
  }

  me.getUserById = function(id)
  {
    for (var i in me.member)
    {
      if (me.member[i].id == id)
        return me.member[i];
    }
    return null;
  };

  me.getUserByFBId = function(fbid)
  {
    for (var i in me.member)
    {
      if (me.member[i].fbid == fbid)
        return me.member[i];
    }
    return null;
  };

  me.getUserByName = function(name)
  {
    for (var i in me.member)
    {
      if (me.member[i].name == name)
        return me.member[i];
    }
    return null;
  };

  me.removeMember = function(user, done)
  {
    console.log(user);
    var index = $.inArray(user, me.member);
    console.log(index);
    if (index == -1)
      return;
    me.member.splice(index, 1);
    console.log(me.member);

    var data = {};
    data.id = user.id;
    console.log(data);
    $.post("/api/removeUser.php", data, done, "json");
  };

  me.addMember = function(user, done)
  {
    var data = user.getData();
    $.post("/api/createUser.php", data, function(result)
    {
      if (result.code == 0)
      {
        user.id = result.id;
        me.member.push(user);
      }
      done(result);
    }, "json");
  }
}

function User(data)
{
  var me = this;
  if (data)
  {
    me.id = data.id;
    me.fbid = data.fbid;
    me.name = data.name;
    me.club_id = data.club_id;
    me.title = data.title;
    me.background = data.background;
    me.birth_year = data.birth_year;
    me.birth_month = data.birth_month;
    me.note = data.note;
  }
  else
  {
    me.id = 0;
    me.fbid = "";
    me.name = "";
    me.club_id = -1;
    me.title = "";
    me.background = 0;
    me.birth_year = 1984;
    me.birth_month = 1
  }

  me.getFBUrl = function()
  {
    return "https://www.facebook.com/" + me.fbid;
  };
  me.getPicUrl = function()
  {
    return "http://graph.facebook.com/" + me.fbid + "/picture?type=square";
  };
  me.getData = function()
  {
    var data = {};
    data.id = me.id;
    data.fbid = me.fbid;
    data.name = me.name;
    data.club_id = me.club_id;
    data.title = me.title;
    data.background = me.background;
    data.birth_year = me.birth_year;
    data.birth_month = me.birth_month;
    data.note = me.note;
    return data;
  };
  me.hasFBId = function()
  {
    return me.fbid > 0;
  };
  me.isNew = function()
  {
    return me.id == 0;
  };
  me.save = function(done)
  {
    var data = me.getData();
    $.post("/api/updateUser.php", data, done, "json");
  };
}

function Page(data)
{
  var me = this;
  me.id = data.id;
  me.owner = data.owner; // fbid array
  
  me.hasOwner = function(fbid)
  {
    return ($.inArray(fbid, me.owner) != -1);
  };

  me.save = function(done)
  {
    var data = {};
    data.id = me.id;
    data.owner = me.owner;
    $.post("/api/updatePage.php", data, done, "json");
  };
}
function Meeting(data)
{
  var me = this;
  if (data)
  {
    me.id = parseInt(data.id);
    me.date = data.date;
    me.topic = data.topic;
    me.type = parseInt(data.type);
    me.attendee = data.attendee;
    me.absent = data.absent;
  }
  else
  {
    me.id = 0;
    var today = new Date();
    var month = today.getMonth() + 1;
    month = month > 9? month: "0" + month;
    var day = today.getDate();
    day = day > 10? day: "0" + day;
    me.date = today.getFullYear() + "/" + month + "/" + day;
    me.topic = "";
    me.type = 1;
    me.attendee = new Array();
    me.absent = new Array();
  }

  me.getData = function()
  {
    var data = {};
    data.id = me.id;
    data.date = me.date;
    data.topic = me.topic;
    data.type = me.type;
    data.attendee = me.attendee.slice(0);
    data.absent = me.absent.slice(0);
    return data;
  };

  me.getAttendanceRate = function()
  {
    if (me.attendee.length + me.absent.length == 0)
      return "-";
    else
      return parseInt(me.attendee.length * 100 / (me.attendee.length + me.absent.length)) + "%";
  };

  me.getTypeString = function()
  {
    switch (me.type)
    {
      case 1: return "團務";
      case 2: return "國際";
      case 3: return "職業";
      case 4: return "社服";
      case 5: return "公關";
      case 6: return "其他";
      default: return "";
    }
  };

  me.remove = function(done)
  {
    $.post("/api/removeMeeting.php", {"id":me.id}, done, "json");
  };

  me.save = function(original_id, done)
  {
    var data = me.getData();
    data.original_id = original_id;
    $.post("/api/saveMeeting.php", data, done, "json");
  };
}

function Event(data)
{
  var me = this;
  if (data)
  {
    me.id = parseInt(data.id);
    me.date = data.date;
    me.topic = data.topic;
    me.location = data.location;
    me.partner = data.partner;
    me.note = data.note;
  }
  else
  {
    me.id = 0;
    var today = new Date();
    var month = today.getMonth() + 1;
    month = month > 9? month: "0" + month;
    var day = today.getDate();
    day = day > 10? day: "0" + day;
    me.date = today.getFullYear() + "/" + month + "/" + day;
    me.topic = "";
    me.location = "";
    me.partner = "";
    me.note = "";
  }

  me.getData = function()
  {
    var data = {};
    data.id = me.id;
    data.date = me.date;
    data.topic = me.topic;
    data.location = me.location;
    data.partner = me.partner;
    data.note = me.note;
    return data;
  };

  me.remove = function(done)
  {
    $.post("/api/removeEvent.php", {"id":me.id}, done, "json");
  };

  me.save = function(done)
  {
    var data = me.getData();
    console.log(data);
    $.post("/api/saveEvent.php", data, done, "json");
  };
}

function EventResource(data)
{
  var me = this;
  if (data)
  {
    me.id = parseInt(data.id);
    me.event_id = data.event_id;
    me.type = data.type;
    me.topic = data.topic;
    me.fbid = data.fbid;
    me.last_update = data.last_update;
    me.link = data.link;
  }
  else
  {
    me.id = 0;
    me.event_id = 0;
    me.type = 1;
    me.topic = "";
    me.fbid = "";
    me.last_update = 0;
    me.link = "";
  }

  me.getData = function()
  {
    var data = {};
    data.id = me.id;
    data.topic = me.topic;
    data.fbid = data.fbid;
    data.last_update = me.last_update;
    data.link = me.link;
    return data;
  };

  me.getTypeString = function()
  {
    switch(me.type)
    {
      case 1: return "檔案";
      case 2: return "照片";
      case 3: return "網址";
      default:
        return "";
    }
  }

  me.remove = function(done)
  {
    $.post("/api/removeEventResource.php", {"id":me.id}, done, "json");
    //done({code:0});
  };

  // me.save = function(done)
  // {
  //   var data = me.getData();
  //   $.post("/api/saveEvent.php", data, done, "json");
  // };
}
// UI objects

function PageInfo(element)
{
  var me = this;
  me.element = {};
  me.element.root = $(element);
  me.element.owner_list = me.element.root.find("> [data-ref='owner']");
  me.element.editor = me.element.root.find("[data-ref='editor']");
  me.data = {};

  me.setData = function(session)
  {
    me.data.club = session.club;
    me.data.owner = session.page.owner;
    me.data.user = session.user;
    me.data.page = session.page;
    me.refresh();
  };

  me.setOwner = function(owner)
  {
    me.data.owner = owner;
    me.refresh();
  };

  me.refresh = function()
  {
    var owner_name = new Array();
    for (var i = 0; i < me.data.owner.length; ++i)
    {
      var user = me.data.club.getUserByFBId(me.data.owner[i]);
      if (!user)
      {
        console.warn("no such user: " + me.data.owner[i]);       
        continue;
      }
      owner_name.push(user.name);
    }
    me.element.owner_list.text(owner_name.join(", "));
  };

  var addOwner = function(user)
  {
    var $owner = me.element.editor.find("[data-ref='owner'] > p.template").clone();
    $owner.removeClass("template");
    $owner.attr("data-src", user.fbid)
    $owner.find("[data-ref='name']").text(user.name);
    $owner.find("[data-link='remove']").click(function(e){
      $(e.target).parents("[data-src]").remove();
    });
    me.element.editor.find("[data-ref='owner']").append($owner);
  };

  var initEditor = function()
  {
    var getCurrentOwners = function()
    {
      var owners = [];
      me.element.editor.find("[data-ref='owner'] > p:not(.template)").each(function()
      {
        owners.push($(this).attr("data-src"));
      });
      return owners;
    };
    var getAvailableUsers = function()
    {
      var users = [];
      var owners = getCurrentOwners();
      for (var i in me.data.club.member)
      {
        if (!me.data.club.member[i].hasFBId())
          continue;
        if ($.inArray(me.data.club.member[i].fbid, owners) == -1)
          users.push(me.data.club.member[i].name);
      }
      return users;
    };

    me.element.editor.find("input").typeahead({
      source: function()
      {
        return getAvailableUsers();
      },
      items: 4,
      updater: function(item)
      {
        var owner = me.data.club.getUserByName(item);
        addOwner(owner);
      }
    });

    me.element.editor.find("[data-link='save']").click(function()
    {
      var owners = getCurrentOwners();
      console.log(owners);
      if (owners.length == 0)
      {
        alert("不能沒有頁面管理員喔！");
        return;
      }
      me.data.page.owner = owners;
      me.data.page.save(function(e)
      {
        console.log(e);
        if (e.code != 0)
        {
          alert("啊，壞掉了！\n\n錯誤碼：" + e.code);
          return;
        }
        console.log(e);
        me.setOwner(owners);
        if ($.inArray(me.data.user.fbid, owners) == -1)
        {
          location.reload();
        }
        me.element.editor.modal('hide');        
      })
    });
  };

  var showEditor = function()
  {
    me.element.editor.find("[data-ref='owner'] > p:not(.template)").remove();
    for (var i = 0; i < me.data.owner.length; ++i)
    {
      var user = me.data.club.getUserByFBId(me.data.owner[i]);
      if (!user)
      {
        console.warn("no such user: " + me.data.owner[i]);
        continue;
      }
      addOwner(user);
    }
    me.element.editor.modal();
  };

  initEditor();
  me.element.root.find("[data-link='edit']").click(showEditor);
}
