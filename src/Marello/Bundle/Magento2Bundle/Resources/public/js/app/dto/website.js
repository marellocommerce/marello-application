define(function() {
    'use strict';

    return class {
        constructor(id, name) {
            this.id = id;
            this.name = name;
        }

        getId() {
            return Number.parseInt(this.id);
        }

        getName() {
            return this.name;
        }
    };
});
