# Extends a Payment

## entry

Where the all user interfaces locates. 

Placed at `/entries` and extends `fk\pay\entries\Entry` and implements all its methods

### methods

- `pay`

    Invoked when `fk\pay\Component::pay` is called.

## lib

Create a lib contains all the helper class for the payment `Entry`, which is optional as long as the `Entry` can finish its job. 

Placed at `/lib`, anything that satisfy the `entry` or `notify`

### properties

- `config` The configuration passed to this payment

## notify

This is the class that used for asynchronous notification of a payment's result.

placed at `/notify` implements `fk\pay\notify\NotifyInterface`