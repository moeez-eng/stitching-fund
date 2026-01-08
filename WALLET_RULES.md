# Wallet System - 4 Key Metrics & Calculation Rule

## The 4 Truths (Key Metrics)

The wallet system is built on these 4 fundamental metrics:

1. **Lifetime Deposited** - Total amount ever deposited into the wallet
2. **Active Invested** - Currently invested amount in active investment pools
3. **Total Returned** - Total returns/profits received from investments
4. **Available Balance** - Current available balance for new investments

## Golden Rule

**Available Balance = Deposited + Returned - Active Invested**

This rule is the foundation of all wallet calculations and must never be violated.

## Implementation Details

- **Lifetime Deposited**: `wallet.amount` field in database
- **Active Invested**: Sum of all `wallet_allocation.amount` where status is active
- **Total Returned**: Currently 0 (to be implemented when returns system is added)
- **Available Balance**: Calculated using the golden rule in Wallet model

## Model Methods

The following methods are implemented in `App\Models\Wallet`:

```php
getLifetimeDepositedAttribute() // Returns total deposited amount
getActiveInvestedAttribute()    // Returns currently invested amount  
getTotalReturnedAttribute()     // Returns total returns (currently 0)
getAvailableBalanceAttribute()  // Applies the golden rule
```

## UI Display

The wallet interface displays all 4 metrics in a 4-column grid:
- Lifetime Deposited
- Active Invested  
- Total Returned
- Available Balance

The main "Available Balance" display uses the calculated value from the golden rule.

## Future Considerations

- When implementing returns/profits system, update `getTotalReturnedAttribute()`
- The golden rule must remain consistent across all calculations
- All wallet status calculations (healthy/low/critical) are based on the available balance from the golden rule
