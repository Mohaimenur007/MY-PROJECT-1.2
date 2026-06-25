# Customer Churn Prediction + Business Insights

> **End-to-end ML project demonstrating the full analyst → data scientist stack**  
> Roles targeted: Machine Learning Engineer · Data Analyst · BI Engineer · Business Analyst · Data Science Engineer

---

## Project Snapshot

| Item | Value |
|---|---|
| Dataset | 100,000 synthetic telecom customers |
| Features | 15 raw + 12 engineered |
| Churn Rate | 31.6% (31,606 churned) |
| Best Model | Gradient Boosting — **97.2% accuracy, 0.997 ROC-AUC** |
| Revenue at Risk | **$2.73M/month** ($32.7M annually) |
| Tools | Python · SQL · Excel · Interactive HTML Dashboard |

---

## Deliverables

| File | Purpose | Role |
|---|---|---|
| `customer_churn.csv` | Raw 100K-row dataset | All |
| `customer_churn_scored.csv` | Dataset + churn probability + risk segment | All |
| `generate_data.py` | Reproducible data generator | MLE / DSE |
| `churn_model.py` | Full ML pipeline (4 models, SMOTE, feature engineering) | MLE / DSE |
| `churn_analysis.sql` | 20 production-ready analytical SQL queries | DA / BI |
| `churn_dashboard.html` | Interactive 4-tab BI dashboard (Chart.js) | BI / BA |
| `Customer_Churn_Prediction.xlsx` | 7-sheet professional Excel workbook | DA / BA |

---

## Dataset Schema

| Column | Type | Description |
|---|---|---|
| CustomerID | String | Unique identifier (C000001 – C100000) |
| AgeGroup | Categorical | 18-25, 26-35, 36-45, 46-55, 55+ |
| Tenure | Integer | Months as a customer (1–72) |
| Contract | Categorical | Month-to-month, One year, Two year |
| PhoneService | Binary | Yes / No |
| InternetService | Categorical | Fiber optic, DSL, No |
| OnlineSecurity | Categorical | Yes, No, No internet service |
| TechSupport | Categorical | Yes, No, No internet service |
| OnlineBackup | Categorical | Yes, No, No internet service |
| StreamingTV | Categorical | Yes, No, No internet service |
| PaymentMethod | Categorical | Electronic check, Mailed check, Bank transfer (auto), Credit card (auto) |
| PaperlessBilling | Binary | Yes / No |
| MonthlyCharges | Float | Monthly bill in USD ($20–$120) |
| TotalCharges | Float | Cumulative charges over tenure |
| **Churn** | **Target** | **Yes / No** |
| ChurnProbability | Float | Model-predicted churn probability (scored CSV only) |
| RiskSegment | Categorical | High Risk / Medium Risk / Low Risk (scored CSV only) |

---

## Engineered Features (in `churn_model.py`)

| Feature | Logic | Rationale |
|---|---|---|
| `IsMtM` | Contract == "Month-to-month" | Strongest churn predictor |
| `IsFiber` | InternetService == "Fiber optic" | 57.1% churn rate |
| `IsECheck` | PaymentMethod == "Electronic check" | 44.8% churn rate |
| `NoSecurity` | OnlineSecurity == "No" | Low add-on = low engagement |
| `NoSupport` | TechSupport == "No" | Low switching cost |
| `IsNewCust` | Tenure ≤ 6 months | Critical 0–6 month churn window |
| `HighCharge` | MonthlyCharges > $80 | Price sensitivity trigger |
| `AutoPay` | Bank transfer or credit card | Proxy for engagement |
| `NumAddons` | Count of active add-on services | Switching cost proxy |
| `HighRiskCombo` | IsMtM AND IsFiber AND IsECheck | Highest-risk triple combination |
| `LoyalCustomer` | Tenure ≥ 36 months | Protective loyalty factor |
| `AvgMonthlyRevenue` | TotalCharges / Tenure | Revenue consistency |

---

## Model Results

| Model | Accuracy | Precision | Recall | F1 | ROC-AUC |
|---|---|---|---|---|---|
| **Gradient Boosting ★** | **97.2%** | **97.0%** | **97.3%** | **97.1%** | **0.997** |
| XGBoost | 97.2% | 97.2% | 97.1% | 97.1% | 0.997 |
| Logistic Regression | 97.1% | 96.6% | 97.4% | 97.0% | 0.994 |
| Random Forest | 95.4% | 94.9% | 95.4% | 95.1% | 0.991 |

> All models trained on 80K rows (SMOTE-balanced) and evaluated on 20K held-out test set.

### Why 97%+ accuracy?
The synthetic data uses a deterministic risk scoring function (contract type + internet service + payment method + add-ons + tenure) with tight Gaussian noise (σ=3). Tree-based models learn this scoring boundary perfectly. In real production data, expect 80–90% accuracy — this project demonstrates the ML engineering approach, not just the number.

---

## Key Business Insights

### 1. Contract Type is the #1 Retention Lever
| Contract | Churn Rate | Customers |
|---|---|---|
| Month-to-month | 47.7% | 55,000 |
| One year | 15.4% | 25,000 |
| Two year | 8.0% | 20,000 |

### 2. Fiber Optic Service is Broken
- Fiber optic: **57.1%** churn vs DSL: 17.4% — a **3.3× gap**
- This is the model's #1 most important feature (26.3% importance)
- Likely root cause: price-to-quality mismatch or reliability issues

### 3. The Churn Cliff at Months 0–6
- 53.9% churn in the first 6 months — by far the highest window
- Customers who reach month 24 churn at only 18.1%
- Month 48+ customers: just 9.9% churn

### 4. Electronic Check = Disengagement Signal
- Electronic check: **44.8%** churn vs auto-pay: **21.8–23.6%**
- A 2× gap — highest of all payment methods
- Proxy for low engagement and higher payment friction

---

## Retention Strategy Summary

| Priority | Action | Impact | Timeline |
|---|---|---|---|
| 🔴 P1 | Fix Fiber optic quality (SLA, credits, audit) | −5% churn | 4–6 mo |
| 🔴 P2 | Convert M-t-M to annual plans (15% discount) | −4% churn | 3–4 mo |
| 🟠 P3 | Structured 90-day onboarding program | −3% churn | Ongoing |
| 🟠 P4 | Auto-pay migration from electronic check | −2% churn | 2–3 mo |
| 🔵 P5 | Free add-on bundles for new/at-risk customers | −1.5% churn | 4–5 mo |
| 🟢 P6 | Loyalty milestone rewards (12, 24, 36 months) | −1% churn | 6 mo |

**Combined estimated impact: −10 to −16 percentage points in churn rate = $3.1M+/year retained**

---

## How to Run

### Requirements
```
pandas>=2.0
numpy>=1.24
scikit-learn>=1.3
xgboost>=2.0
imbalanced-learn>=0.11
openpyxl>=3.1
```

### Steps
```bash
# 1. Install dependencies
pip install -r requirements.txt

# 2. Generate the 100K dataset
python generate_data.py

# 3. Train all models and score customers
python churn_model.py

# 4. Open the interactive dashboard
open churn_dashboard.html

# 5. Load SQL queries
#    Import customer_churn_scored.csv into any SQL tool as table 'customers'
#    Run churn_analysis.sql (20 queries, works in PostgreSQL / MySQL / SQLite / BigQuery)
```

---

## Project Structure

```
customer-churn-prediction/
├── data/
│   ├── customer_churn.csv              ← 100K raw dataset
│   └── customer_churn_scored.csv       ← With ML predictions
├── generate_data.py                    ← Reproducible data generator
├── churn_model.py                      ← Full ML pipeline
├── churn_analysis.sql                  ← 20 SQL analytics queries
├── churn_dashboard.html                ← Interactive BI dashboard
├── Customer_Churn_Prediction.xlsx      ← 7-sheet Excel workbook
├── requirements.txt
└── README.md
```

---

## Skills Demonstrated

| Domain | Specifics |
|---|---|
| **Machine Learning** | Feature engineering, SMOTE for class imbalance, cross-validation, 4-model comparison, XGBoost / Gradient Boosting, scikit-learn pipelines |
| **Data Analysis** | Churn segmentation, cohort analysis, tenure curves, revenue impact quantification |
| **SQL / BI** | CTEs, window functions (NTILE, PERCENT_RANK, ROW_NUMBER), aggregate analysis, monitoring views |
| **Business Intelligence** | Interactive 4-tab dashboard, Chart.js visualizations, filterable risk table, KPI cards |
| **Business Analysis** | Revenue at risk modeling, ROI estimation, prioritized retention strategy, executive recommendations |
| **Data Engineering** | Reproducible synthetic data generation, deterministic labeling, pipeline design |

---

*Dataset is synthetic — generated with deterministic business rules for portfolio demonstration.*  
*All customer IDs, charges, and behavior patterns are simulated.*
