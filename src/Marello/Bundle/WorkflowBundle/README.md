# MarelloWorkflowBundle

MarelloWorkflowBundle inherits OroWorkflowBundle and provides tools and solutions related to workflow process.

## Datagrid mass actions

### Workflow transit mass action

The action allows you to massively use workflow transitions for those recordings that support.

```
datagrids:
    test-datagrid:
        ...
        mass_actions:
            workflowtransit:
                type: workflowtransit
                label: Do transition
                icon: <icon>
                acl_resource: <acl-resource>      # Optional
                handler: <handler-service-name>   # Optional
                data_identifier: <id>             # Required. Data identifier is a field that is used to search entities
                entity_name: <entity-class>       # Required. Entity name must be the same as the main datagrid's entity name
                workflow: <workflow-name>         # RequiredIs used to determinate how to process request. Sync or async if the total recods count is higher than batch size. Ensure that workflow with such name exists, otherwise the action will not be displayed
                transition: <transition-name>     # Required. Ensure that transition with such name exists, otherwise the action will not be displayed
                batch_size: 10                    # Optional. Used to determine how a request will be processed. Sync or async if the total number of records exceeds the batch size
                report_template: <template-name>  # Optional. Used for an email report to be sent after async processing or after sync processing with failed records
```
