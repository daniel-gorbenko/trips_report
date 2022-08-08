## To run the script in command line

```
php app.php input.csv ouptut.csv DURATION_TYPE
```

DURATION_TYPE:
* d - show duration in duration time format, like `01:15:30`
* s - show duration in seconds, like `3547`

## Example

This will generate `output.csv` file with duration in seconds based on the input `example-input.csv`:

```
php app.php example-input.csv ouptut.csv s
```

## How to verify that it works correctly

Fill an input file manually with different types of scenarios, check by yourself and then run script and compare results. May be I will add tests in the future.

## Efficiency

### Time complexity

Worst case: one driver carrying one passenger - `O(n + n^2/2)`

### RAM

Worst case: all `n` trips are stored in memory + `n` drivers are stored in memory. How much is allocated for each element - did not look.