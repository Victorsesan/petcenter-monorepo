from pathlib import Path
import csv
import matplotlib.pyplot as plt
from matplotlib.patches import FancyBboxPatch

OUT = Path(__file__).parent

metrics = [
    ("PHPUnit tests", 8, 8),
    ("PHPUnit assertions", 20, 20),
    ("Merchant PHP cases", 14, 14),
    ("Merchant JS cases", 17, 17),
    ("Admin/apply PHP lint", 60, 60),
]
http_counts = {200: 6, 400: 1, 401: 5, 403: 2, 405: 6}
http_labels = {
    200: "Successful public/endpoint responses",
    400: "Validation rejection",
    401: "Authentication rejection",
    403: "Protected-file denial",
    405: "Method rejection",
}
status = [
    ("Resolved", 5),
    ("Pending / integrator", 3),
    ("Environment limitation", 1),
]

with (OUT / "MANUS-09-QA-metrics.csv").open("w", newline="") as f:
    w = csv.writer(f)
    w.writerow(["metric", "passed", "total", "rate"])
    for name, passed, total in metrics:
        w.writerow([name, passed, total, passed / total])
    w.writerow([])
    w.writerow(["http_status", "count", "label"])
    for code, count in http_counts.items():
        w.writerow([code, count, http_labels[code]])
    w.writerow([])
    w.writerow(["review_status", "count"])
    for name, count in status:
        w.writerow([name, count])

plt.style.use("seaborn-v0_8-whitegrid")
fig = plt.figure(figsize=(14, 8.5), facecolor="#f6f8fb")
gs = fig.add_gridspec(2, 2, height_ratios=[1.15, 1], hspace=0.38, wspace=0.24)
fig.text(0.055, 0.955, "MANUS-09 QA Dashboard", fontsize=24, fontweight="bold", color="#14213d")
fig.text(0.055, 0.922, "Verified repository and live deployment evidence · 2026-11-09", fontsize=11, color="#596579")

# Test panel
ax1 = fig.add_subplot(gs[0, 0])
names = [x[0] for x in metrics]
vals = [x[1] for x in metrics]
totals = [x[2] for x in metrics]
y = range(len(names))
ax1.barh(list(y), totals, color="#dbe4f0", height=0.58, label="Total")
ax1.barh(list(y), vals, color="#1f9d72", height=0.58, label="Passed")
ax1.set_yticks(list(y), names)
ax1.set_xlim(0, max(totals) * 1.18)
ax1.invert_yaxis()
ax1.set_title("Automated validation", loc="left", fontweight="bold", color="#14213d")
ax1.set_xlabel("Passed / total")
for i, (v, t) in enumerate(zip(vals, totals)):
    ax1.text(v + max(totals) * 0.02, i, f"{v}/{t} · 100%", va="center", fontsize=9, color="#1f5e4a")
ax1.legend(frameon=False, loc="lower right")

# HTTP panel
ax2 = fig.add_subplot(gs[0, 1])
colors = {200: "#1f9d72", 400: "#f4a261", 401: "#e76f51", 403: "#8e5ea2", 405: "#457b9d"}
codes = list(http_counts)
counts = [http_counts[c] for c in codes]
bars = ax2.bar([str(c) for c in codes], counts, color=[colors[c] for c in codes], width=0.62)
ax2.set_title("Live HTTP security matrix", loc="left", fontweight="bold", color="#14213d")
ax2.set_xlabel("HTTP status")
ax2.set_ylabel("Routes checked")
ax2.set_ylim(0, max(counts) + 1.8)
for b, c in zip(bars, counts):
    ax2.text(b.get_x() + b.get_width()/2, c + 0.15, str(c), ha="center", fontweight="bold")
ax2.text(0.02, -0.27, "20 cache-busted route/guard checks; no mutation requests", transform=ax2.transAxes, fontsize=8.5, color="#596579")

# Review status panel
ax3 = fig.add_subplot(gs[1, 0])
labels = [x[0] for x in status]
values = [x[1] for x in status]
wedges, _ = ax3.pie(values, colors=["#1f9d72", "#e9a23b", "#8b95a5"], startangle=90, wedgeprops={"width":0.42, "edgecolor":"#f6f8fb"})
ax3.set_title("MANUS-09 review disposition", loc="left", fontweight="bold", color="#14213d")
ax3.legend(wedges, [f"{l}: {v}" for l, v in status], loc="lower center", bbox_to_anchor=(0.5, -0.12), frameon=False, ncol=1)

# Deployment parity / callouts panel
ax4 = fig.add_subplot(gs[1, 1])
ax4.axis("off")
ax4.set_title("Deployment parity and controls", loc="left", fontweight="bold", color="#14213d")
callouts = [
    ("0", "missing public paths"),
    ("0", "extra deploy paths"),
    ("0", "content mismatches"),
    ("403", "/apply/config.php and /admin/.env"),
    ("PASS", "8 PHPUnit tests / 20 assertions"),
]
for i, (big, small) in enumerate(callouts):
    y0 = 0.87 - i * 0.18
    ax4.add_patch(FancyBboxPatch((0.02, y0 - 0.065), 0.18, 0.11, boxstyle="round,pad=0.012,rounding_size=0.02", facecolor="#e8f5ef" if big in ("0", "PASS") else "#f1e9f5", edgecolor="none"))
    ax4.text(0.11, y0, big, ha="center", va="center", fontsize=15, fontweight="bold", color="#1f7659" if big in ("0", "PASS") else "#70468a")
    ax4.text(0.25, y0, small, va="center", fontsize=10, color="#334155")

fig.text(0.055, 0.025, "Sources: reports/second-round/MANUS-09.md and final live smoke-test matrix. Green = passed/closed; amber/gray = follow-up or environment dependency.", fontsize=8.5, color="#596579")
fig.savefig(OUT / "MANUS-09-QA-dashboard.png", dpi=180, bbox_inches="tight")
print(OUT / "MANUS-09-QA-dashboard.png")
print(OUT / "MANUS-09-QA-metrics.csv")
