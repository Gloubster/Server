(function(){
    $(document).ready(function() {

        var wsuri = "ws://local.gloubster:9990";

        var Worker = Backbone.Model.extend({

        });

        var WorkersCollection =  Backbone.Collection.extend({
            model: Worker
        });

        var WorkerView = Backbone.View.extend({

            render: function() {
                var data = this.model.toJSON();
                data.memory = filesize(data.memory);
                data.startTime = relativeDate(data.startTime);
                data.reportTime = relativeDate(data.reportTime * 1000);

                var template = Hogan.compile("Using {{ memory }} at <em class='pretty-date' gloubster-date='{{ reportTime }}'></em>");
                $(this.el).html(template.render(data));

                return this;
            }
        });

        var Workers = new WorkersCollection();

        ab.connect(wsuri,
            function (session) {
                console.log('connected');
                session.subscribe("http://phraseanet.com/gloubster/monitor",
                    function (topic, event) {
                        var presence = eval('(' + event + ')');

                        if (Workers.get(presence.id)) {
                            Workers.get(presence.id).set('reportTime', presence.reportTime);
                            Workers.get(presence.id).set('memory', presence.memory);
                        } else {
                            Workers.add(presence);
                            var obj = new WorkerView({model: Workers.get(presence.id)});

                            Workers.get(presence.id).on('change', function(model) {
                                obj.render();
                            });

                            obj.render();
                            $('#workers').append(obj.$el);
                        }
                    });
            },
            function (code, reason) {
                console.log("session has gone :( reason is : " + reason);
            }
        );

    });
})();
