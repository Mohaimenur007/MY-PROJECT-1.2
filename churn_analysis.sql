-- ============================================================
-- Customer Churn Prediction — SQL Analytics Suite
-- Project   : Telecom Customer Churn Intelligence
-- Dataset   : 100,000 customers · 31.6% churn rate
-- Models    : Gradient Boosting / XGBoost (97.2% accuracy)
-- Table     : customers (import customer_churn_scored.csv)
-- ============================================================

-- ════════════════════════════════════════════════════════════
-- 1. EXECUTIVE KPIs
-- ════════════════════════════════════════════════════════════
SELECT
    COUNT(*)                                                         AS total_customers,
    SUM(CASE WHEN Churn = 'Yes' THEN 1 ELSE 0 END)                  AS churned,
    SUM(CASE WHEN Churn = 'No'  THEN 1 ELSE 0 END)                  AS retained,
    ROUND(100.0 * SUM(CASE WHEN Churn = 'Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_monthly_charge,
    ROUND(SUM(MonthlyCharges), 2)                                    AS total_monthly_revenue,
    ROUND(SUM(CASE WHEN Churn = 'Yes' THEN MonthlyCharges ELSE 0 END), 2)
                                                                     AS monthly_revenue_lost,
    ROUND(AVG(CASE WHEN Churn = 'Yes' THEN Tenure END), 1)          AS avg_churn_tenure_mo,
    ROUND(AVG(CASE WHEN Churn = 'No'  THEN Tenure END), 1)          AS avg_retain_tenure_mo
FROM customers;


-- ════════════════════════════════════════════════════════════
-- 2. CHURN RATE BY CONTRACT TYPE
-- ════════════════════════════════════════════════════════════
SELECT
    Contract,
    COUNT(*)                                                         AS total,
    SUM(CASE WHEN Churn = 'Yes' THEN 1 ELSE 0 END)                  AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_monthly_charge,
    ROUND(SUM(CASE WHEN Churn='Yes' THEN MonthlyCharges ELSE 0 END) * 12, 2)
                                                                     AS annual_revenue_at_risk
FROM customers
GROUP BY Contract
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 3. CHURN BY INTERNET SERVICE
-- ════════════════════════════════════════════════════════════
SELECT
    InternetService,
    COUNT(*)                                                         AS total_customers,
    SUM(CASE WHEN Churn = 'Yes' THEN 1 ELSE 0 END)                  AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_monthly_charge,
    ROUND(SUM(CASE WHEN Churn='Yes' THEN MonthlyCharges ELSE 0 END), 2)
                                                                     AS monthly_revenue_lost
FROM customers
GROUP BY InternetService
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 4. CHURN BY PAYMENT METHOD
-- ════════════════════════════════════════════════════════════
SELECT
    PaymentMethod,
    COUNT(*)                                                         AS total,
    SUM(CASE WHEN Churn = 'Yes' THEN 1 ELSE 0 END)                  AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_charge,
    CASE
        WHEN PaymentMethod IN ('Bank transfer (automatic)',
                               'Credit card (automatic)') THEN 'Auto-Pay'
        ELSE 'Manual Pay'
    END                                                              AS pay_type
FROM customers
GROUP BY PaymentMethod
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 5. CHURN CLIFF — TENURE BUCKET ANALYSIS
-- ════════════════════════════════════════════════════════════
SELECT
    CASE
        WHEN Tenure BETWEEN  1 AND  6  THEN '01 – 06 months  ← Critical window'
        WHEN Tenure BETWEEN  7 AND 12  THEN '07 – 12 months  ← Still high risk'
        WHEN Tenure BETWEEN 13 AND 24  THEN '13 – 24 months  ← Stabilising'
        WHEN Tenure BETWEEN 25 AND 48  THEN '25 – 48 months  ← Loyal segment'
        ELSE                                '49 – 72 months  ← Champions'
    END                                                              AS tenure_bucket,
    COUNT(*)                                                         AS customers,
    SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)                    AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_charge
FROM customers
GROUP BY tenure_bucket
ORDER BY tenure_bucket;


-- ════════════════════════════════════════════════════════════
-- 6. REVENUE AT RISK BY PREDICTED RISK SEGMENT
-- ════════════════════════════════════════════════════════════
SELECT
    RiskSegment,
    COUNT(*)                                                         AS customer_count,
    ROUND(SUM(MonthlyCharges), 2)                                    AS monthly_revenue,
    ROUND(SUM(MonthlyCharges) * 12, 2)                              AS annual_revenue,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_monthly_charge,
    ROUND(AVG(ChurnProbability) * 100, 2)                           AS avg_churn_prob_pct,
    ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER (), 2)              AS pct_of_base
FROM customers
GROUP BY RiskSegment
ORDER BY avg_churn_prob_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 7. HIGH-VALUE CUSTOMERS AT CRITICAL CHURN RISK
--    Priority outreach list for retention team
-- ════════════════════════════════════════════════════════════
SELECT
    CustomerID,
    Contract,
    InternetService,
    PaymentMethod,
    Tenure,
    MonthlyCharges,
    ROUND(ChurnProbability * 100, 1)        AS churn_prob_pct,
    ROUND(MonthlyCharges * 12, 2)           AS estimated_annual_value,
    CASE
        WHEN ChurnProbability >= 0.90 THEN 'CRITICAL — Call within 48 hrs'
        WHEN ChurnProbability >= 0.75 THEN 'HIGH — Email this week'
        ELSE                               'ELEVATED — Add to campaign'
    END                                     AS action_flag
FROM customers
WHERE ChurnProbability >= 0.70
  AND MonthlyCharges   >= 70
  AND Churn = 'No'           -- Still retained — intervene now
ORDER BY ChurnProbability DESC, MonthlyCharges DESC
LIMIT 100;


-- ════════════════════════════════════════════════════════════
-- 8. HIGHEST-RISK CROSS-SEGMENT: M-T-M × FIBER OPTIC
--    Model's #1 and #2 features combined
-- ════════════════════════════════════════════════════════════
WITH cross_seg AS (
    SELECT
        Contract,
        InternetService,
        COUNT(*)                                                     AS total,
        SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)                AS churned,
        ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
                   / COUNT(*), 2)                                    AS churn_rate_pct,
        ROUND(AVG(MonthlyCharges), 2)                               AS avg_charge,
        ROUND(SUM(MonthlyCharges), 2)                               AS total_monthly_rev
    FROM customers
    GROUP BY Contract, InternetService
)
SELECT *,
    ROUND(total_monthly_rev * (churn_rate_pct/100), 2)              AS est_monthly_loss
FROM cross_seg
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 9. ADD-ON SERVICES IMPACT ON CHURN
-- ════════════════════════════════════════════════════════════
WITH addon_counts AS (
    SELECT
        CustomerID, Churn, MonthlyCharges,
        (CASE WHEN OnlineSecurity = 'Yes' THEN 1 ELSE 0 END +
         CASE WHEN TechSupport    = 'Yes' THEN 1 ELSE 0 END +
         CASE WHEN OnlineBackup   = 'Yes' THEN 1 ELSE 0 END +
         CASE WHEN StreamingTV    = 'Yes' THEN 1 ELSE 0 END)        AS num_addons
    FROM customers
    WHERE InternetService != 'No'
)
SELECT
    num_addons,
    COUNT(*)                                                         AS customers,
    SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)                    AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_charge
FROM addon_counts
GROUP BY num_addons
ORDER BY num_addons;


-- ════════════════════════════════════════════════════════════
-- 10. SECURITY ADD-ON EFFECT (ISOLATION)
-- ════════════════════════════════════════════════════════════
SELECT
    OnlineSecurity,
    TechSupport,
    COUNT(*)                                                         AS total,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_charge
FROM customers
WHERE InternetService != 'No'
GROUP BY OnlineSecurity, TechSupport
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 11. CHURN BY AGE GROUP
-- ════════════════════════════════════════════════════════════
SELECT
    AgeGroup,
    COUNT(*)                                                         AS total,
    SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)                    AS churned,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct,
    ROUND(AVG(Tenure), 1)                                           AS avg_tenure,
    ROUND(AVG(MonthlyCharges), 2)                                   AS avg_charge
FROM customers
GROUP BY AgeGroup
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 12. ELECTRONIC CHECK → AUTO-PAY MIGRATION IMPACT ESTIMATE
-- ════════════════════════════════════════════════════════════
WITH echeck AS (
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END) AS churned,
           SUM(MonthlyCharges) AS monthly_rev
    FROM customers WHERE PaymentMethod = 'Electronic check'
),
autopay AS (
    SELECT ROUND(100.0*SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)/COUNT(*),2) AS autopay_churn_rate
    FROM customers
    WHERE PaymentMethod IN ('Bank transfer (automatic)','Credit card (automatic)')
)
SELECT
    e.total                                                          AS echeck_customers,
    e.churned                                                        AS current_churned,
    ROUND(100.0 * e.churned / e.total, 2)                          AS current_churn_rate_pct,
    a.autopay_churn_rate                                            AS if_autopay_churn_rate_pct,
    ROUND(e.total * (100.0 - a.autopay_churn_rate) / 100
          - e.total * (100.0 - (100.0*e.churned/e.total)) / 100, 0)
                                                                     AS additional_customers_saved,
    ROUND(e.monthly_rev * ((100.0*e.churned/e.total) - a.autopay_churn_rate)
          / 100, 2)                                                  AS monthly_revenue_saved
FROM echeck e, autopay a;


-- ════════════════════════════════════════════════════════════
-- 13. CUSTOMER LIFETIME VALUE (CLV) ESTIMATE
-- ════════════════════════════════════════════════════════════
SELECT
    CustomerID,
    Tenure,
    MonthlyCharges,
    TotalCharges,
    ROUND(ChurnProbability * 100, 1)                                AS churn_prob_pct,
    ROUND(MonthlyCharges / NULLIF(ChurnProbability, 0), 2)          AS predicted_ltv_months_remaining,
    ROUND(MonthlyCharges * (1 - ChurnProbability) * 24, 2)          AS estimated_24mo_value,
    CASE
        WHEN MonthlyCharges * (1-ChurnProbability) * 24 > 2000 THEN 'Platinum'
        WHEN MonthlyCharges * (1-ChurnProbability) * 24 > 1000 THEN 'Gold'
        WHEN MonthlyCharges * (1-ChurnProbability) * 24 >  500 THEN 'Silver'
        ELSE 'Standard'
    END                                                              AS clv_tier
FROM customers
WHERE Churn = 'No'
ORDER BY estimated_24mo_value DESC
LIMIT 50;


-- ════════════════════════════════════════════════════════════
-- 14. COHORT SURVIVAL ANALYSIS (Month-by-month churn cliff)
-- ════════════════════════════════════════════════════════════
WITH cohorts AS (
    SELECT
        Tenure,
        COUNT(*) AS cohort_size,
        SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END) AS churned_in_period
    FROM customers
    GROUP BY Tenure
),
running AS (
    SELECT
        Tenure,
        cohort_size,
        churned_in_period,
        ROUND(100.0 * churned_in_period / cohort_size, 2)           AS period_churn_pct,
        SUM(cohort_size) OVER (ORDER BY Tenure)                      AS cumulative_customers,
        SUM(churned_in_period) OVER (ORDER BY Tenure)                AS cumulative_churned
    FROM cohorts
)
SELECT
    Tenure                                                           AS tenure_month,
    cohort_size                                                      AS customers_at_tenure,
    churned_in_period                                                AS churned,
    period_churn_pct                                                 AS churn_rate_pct,
    cumulative_customers,
    cumulative_churned,
    ROUND(100.0 * cumulative_churned / cumulative_customers, 2)      AS cumulative_churn_pct
FROM running
ORDER BY Tenure;


-- ════════════════════════════════════════════════════════════
-- 15. RETENTION ROI CALCULATOR
--     Estimate savings from each strategy
-- ════════════════════════════════════════════════════════════
WITH base AS (
    SELECT
        COUNT(*)                            AS total_customers,
        SUM(MonthlyCharges)                 AS total_monthly_rev,
        AVG(MonthlyCharges)                 AS avg_charge,
        SUM(CASE WHEN Churn='Yes' THEN MonthlyCharges ELSE 0 END)
                                            AS churn_monthly_loss
    FROM customers
)
SELECT
    'Current State'                         AS scenario,
    b.total_customers,
    ROUND(b.churn_monthly_loss, 2)          AS monthly_revenue_lost,
    ROUND(b.churn_monthly_loss * 12, 2)     AS annual_revenue_lost
FROM base b
UNION ALL
SELECT
    'After Strategy P1+P2 (Contract+Fiber)',
    b.total_customers,
    ROUND(b.churn_monthly_loss * 0.72, 2),
    ROUND(b.churn_monthly_loss * 0.72 * 12, 2)
FROM base b
UNION ALL
SELECT
    'After All 6 Strategies Applied',
    b.total_customers,
    ROUND(b.churn_monthly_loss * 0.50, 2),
    ROUND(b.churn_monthly_loss * 0.50 * 12, 2)
FROM base b;


-- ════════════════════════════════════════════════════════════
-- 16. WINDOW FUNCTION — PERCENTILE RANKING OF CHURN PROBABILITY
-- ════════════════════════════════════════════════════════════
SELECT
    CustomerID,
    Contract,
    InternetService,
    MonthlyCharges,
    ROUND(ChurnProbability * 100, 1)            AS churn_prob_pct,
    NTILE(10) OVER (ORDER BY ChurnProbability DESC)
                                                AS risk_decile,
    PERCENT_RANK() OVER (ORDER BY ChurnProbability)
                                                AS percentile_rank,
    ROW_NUMBER() OVER (PARTITION BY Contract ORDER BY ChurnProbability DESC)
                                                AS rank_within_contract
FROM customers
WHERE Churn = 'No'
ORDER BY ChurnProbability DESC
LIMIT 200;


-- ════════════════════════════════════════════════════════════
-- 17. PAPERLESS BILLING & PAYMENT INTERACTION
-- ════════════════════════════════════════════════════════════
SELECT
    PaperlessBilling,
    PaymentMethod,
    COUNT(*)                                                         AS customers,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 2)                                        AS churn_rate_pct
FROM customers
GROUP BY PaperlessBilling, PaymentMethod
ORDER BY churn_rate_pct DESC;


-- ════════════════════════════════════════════════════════════
-- 18. MONTHLY REVENUE LOSS BREAKDOWN
-- ════════════════════════════════════════════════════════════
SELECT
    ROUND(SUM(MonthlyCharges), 2)                                    AS total_monthly_rev_lost,
    ROUND(SUM(MonthlyCharges) * 12, 2)                              AS projected_annual_loss,
    COUNT(*)                                                         AS churned_customers,
    ROUND(AVG(MonthlyCharges), 2)                                    AS avg_charge_per_churner,
    ROUND(AVG(Tenure), 1)                                           AS avg_churn_tenure_months,
    ROUND(MIN(MonthlyCharges), 2)                                    AS min_charge,
    ROUND(MAX(MonthlyCharges), 2)                                    AS max_charge,
    ROUND(STDDEV(MonthlyCharges), 2)                                 AS stddev_charge
FROM customers
WHERE Churn = 'Yes';


-- ════════════════════════════════════════════════════════════
-- 19. MONITORING VIEW — DAILY HIGH-RISK ALERT DASHBOARD
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE VIEW vw_churn_alert_dashboard AS
SELECT
    CustomerID,
    AgeGroup,
    Contract,
    InternetService,
    PaymentMethod,
    Tenure,
    MonthlyCharges,
    ROUND(ChurnProbability * 100, 1)    AS churn_prob_pct,
    RiskSegment,
    CASE
        WHEN ChurnProbability >= 0.90
         AND Churn = 'No'               THEN '🔴 CRITICAL — Immediate Call'
        WHEN ChurnProbability >= 0.75
         AND Churn = 'No'               THEN '🟠 HIGH — Email This Week'
        WHEN ChurnProbability >= 0.60
         AND Churn = 'No'               THEN '🟡 ELEVATED — Add to Campaign'
        WHEN Churn = 'Yes'              THEN '⚫ CHURNED — Post-mortem review'
        ELSE                                 '🟢 STABLE — Routine monitoring'
    END                                 AS action_required,
    ROUND(MonthlyCharges * 12, 2)       AS annual_value
FROM customers
WHERE ChurnProbability >= 0.60 OR Churn = 'Yes'
ORDER BY ChurnProbability DESC;

-- Usage:
SELECT * FROM vw_churn_alert_dashboard WHERE action_required LIKE '%CRITICAL%' LIMIT 50;


-- ════════════════════════════════════════════════════════════
-- 20. FULL SEGMENT MATRIX — ACTIONABLE TARGETING
-- ════════════════════════════════════════════════════════════
WITH segments AS (
    SELECT
        Contract,
        InternetService,
        CASE WHEN PaymentMethod = 'Electronic check' THEN 'E-Check'
             WHEN PaymentMethod IN ('Bank transfer (automatic)',
                                    'Credit card (automatic)') THEN 'Auto-Pay'
             ELSE 'Manual' END                                       AS pay_group,
        CASE WHEN Tenure <= 6 THEN 'New (0-6mo)'
             WHEN Tenure <= 12 THEN 'Early (7-12mo)'
             WHEN Tenure <= 24 THEN 'Mid (13-24mo)'
             ELSE 'Loyal (25mo+)' END                               AS tenure_group,
        Churn, MonthlyCharges
    FROM customers
)
SELECT
    Contract, InternetService, pay_group, tenure_group,
    COUNT(*)                                                         AS customers,
    ROUND(100.0 * SUM(CASE WHEN Churn='Yes' THEN 1 ELSE 0 END)
               / COUNT(*), 1)                                        AS churn_pct,
    ROUND(SUM(MonthlyCharges), 0)                                    AS monthly_revenue,
    ROUND(SUM(CASE WHEN Churn='Yes' THEN MonthlyCharges ELSE 0 END),0)
                                                                     AS revenue_at_risk
FROM segments
GROUP BY Contract, InternetService, pay_group, tenure_group
HAVING COUNT(*) >= 30
ORDER BY churn_pct DESC
LIMIT 30;
