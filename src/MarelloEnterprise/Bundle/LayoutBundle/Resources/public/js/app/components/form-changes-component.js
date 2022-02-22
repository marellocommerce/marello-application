define([
    'oroorganization/js/app/tools/system-access-mode-organization-provider',
    'marellolayout/js/app/components/form-changes-component'
], function(systemAccessModeOrganizationProvider, BaseComponent) {
    'use strict';

    const FormChangesComponent = BaseComponent.extend({
        isGlobalOrg: false,

        /**
         * @inheritdoc
         */
        constructor: function FormChangesComponent(options) {
            FormChangesComponent.__super__.constructor.call(this, options);
        },

        /**
         * @param options
         */
        initialize: function(options) {
            FormChangesComponent.__super__.initialize.call(this, options);
            this.isGlobalOrg = options.viewOptions.isGlobalOrg;
            this.organization = options.organization;
        },

        /**
         * Prepare parameters for getUrl method
         * @returns {*|{}}
         * @private
         */
        _getUrlParams: function() {
            const params = FormChangesComponent.__super__._getUrlParams.call(this);
            let organizationId = systemAccessModeOrganizationProvider.getOrganizationId();
            if (!organizationId && this.isGlobalOrg) {
                organizationId = this.organization;
            }

            if (organizationId) {
                params._sa_org_id = organizationId;
            }

            return params;
        }
    });

    return FormChangesComponent;
});

