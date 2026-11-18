/* HSE CONTROL - Safety Management */
(() => {
  "use strict";

  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => [...root.querySelectorAll(s)];
  const STORAGE = "hse-control-reports-v1";

  const langData = {
    ar: {
      dashboard:"لوحة التحكم", newReport:"تقرير جديد", reports:"التقارير",
      actions:"الإجراءات التصحيحية", subtitle:"نظام إدارة السلامة والصحة والبيئة",
      welcome:"السلامة تبدأ بالوعي",
      welcomeDesc:"تابع الملاحظات والحوادث والإجراءات التصحيحية.",
      total:"إجمالي التقارير", allReports:"جميع السجلات",
      open:"التقارير المفتوحة", needFollow:"تحتاج إلى متابعة",
      highRisk:"مخاطر عالية وحرجة", highRiskDesc:"تقارير عالية الخطورة",
      closed:"التقارير المغلقة", completed:"حالات مكتملة",
      recent:"أحدث التقارير", recentDesc:"آخر الملاحظات الميدانية المسجلة",
      viewAll:"عرض الكل ←", empty:"لا توجد تقارير حتى الآن",
      createFirst:"ابدأ بإنشاء تقرير جديد.",
      riskOverview:"توزيع المخاطر", riskDesc:"التقارير حسب مستوى الخطورة",
      createTitle:"إنشاء تقرير HSE جديد",
      formDesc:"أدخل بيانات الحالة بدقة لضمان المتابعة.",
      general:"المعلومات العامة", generalDesc:"بيانات الملاحظة والموقع",
      type:"نوع التقرير", date:"التاريخ والوقت", site:"الموقع / المنشأة",
      department:"القسم", reporter:"اسم المبلّغ", location:"الموقع الدقيق",
      hazard:"الخطر وتقييم الحالة", hazardDesc:"وصف الملاحظة وتقييم شدتها",
      title:"عنوان التقرير", category:"فئة الخطر", severity:"مستوى الخطورة",
      description:"وصف الحالة", immediate:"الإجراء الفوري المتخذ",
      followup:"الإجراءات التصحيحية والمتابعة",
      followupDesc:"تحديد المسؤول والموعد المستهدف",
      corrective:"الإجراء التصحيحي المقترح",
      responsible:"الشخص المسؤول", dueDate:"تاريخ الاستحقاق",
      clear:"مسح النموذج", save:"حفظ التقرير",
      allTitle:"جميع التقارير",
      allDesc:"ابحث وصنّف تقارير السلامة المسجلة.",
      export:"تصدير CSV", allTypes:"جميع الأنواع",
      allSeverities:"جميع مستويات الخطورة",
      allStatuses:"جميع الحالات", status:"الحالة",
      manage:"الإجراءات", localNote:"البيانات محفوظة محليًا في المتصفح",
      noResults:"لا توجد نتائج مطابقة",
      actionsTitle:"الإجراءات التصحيحية",
      actionsDesc:"متابعة المسؤولين والمواعيد وحالة المعالجة.",
      pending:"إجراءات معلقة", overdue:"إجراءات متأخرة",
      completedActions:"إجراءات مكتملة", actionRegister:"سجل الإجراءات",
      actionRegisterDesc:"الإجراءات المسجلة على التقارير",
      noActions:"لا توجد إجراءات مسجلة.",
      footer:"السلامة مسؤولية الجميع", print:"طباعة", close:"إغلاق",
      low:"منخفض", medium:"متوسط", high:"عالٍ", critical:"حرج",
      Open:"مفتوح", "In Progress":"قيد التنفيذ", Closed:"مغلق",
      view:"عرض", edit:"تعديل", delete:"حذف",
      confirmDelete:"هل تريد حذف هذا التقرير نهائيًا؟",
      saved:"تم حفظ التقرير بنجاح.",
      updated:"تم تحديث التقرير بنجاح.",
      deleted:"تم حذف التقرير.",
      exported:"تم تصدير التقارير.",
      exportEmpty:"لا توجد تقارير لتصديرها.",
      notSpecified:"غير محدد", reportDetails:"تفاصيل التقرير",
      searchPlaceholder:"بحث بالرقم أو العنوان أو الموقع...",
      overdueLabel:"متأخر", closeAction:"إغلاق الإجراء",
      reopen:"إعادة فتح", saveChanges:"حفظ التعديلات",
      statusUpdated:"تم تحديث حالة الإجراء."
    },

    en: {
      dashboard:"Dashboard", newReport:"New Report", reports:"Reports",
      actions:"Corrective Actions",
      subtitle:"Health, Safety & Environment Management System",
      welcome:"Safety Starts with Awareness",
      welcomeDesc:"Track observations, incidents, and corrective actions.",
      total:"Total Reports", allReports:"All records",
      open:"Open Reports", needFollow:"Require follow-up",
      highRisk:"High & Critical Risks", highRiskDesc:"High-risk reports",
      closed:"Closed Reports", completed:"Completed cases",
      recent:"Recent Reports", recentDesc:"Latest recorded field observations",
      viewAll:"View all →", empty:"No reports yet",
      createFirst:"Start by creating a new report.",
      riskOverview:"Risk Distribution", riskDesc:"Reports by severity level",
      createTitle:"Create New HSE Report",
      formDesc:"Enter accurate details to ensure proper follow-up.",
      general:"General Information", generalDesc:"Observation and site details",
      type:"Report Type", date:"Date & Time", site:"Site / Facility",
      department:"Department", reporter:"Reporter Name",
      location:"Exact Location", hazard:"Hazard & Assessment",
      hazardDesc:"Describe the observation and assess severity",
      title:"Report Title", category:"Hazard Category", severity:"Severity",
      description:"Description", immediate:"Immediate Action Taken",
      followup:"Corrective Actions & Follow-up",
      followupDesc:"Assign a responsible person and target date",
      corrective:"Proposed Corrective Action",
      responsible:"Responsible Person", dueDate:"Due Date",
      clear:"Clear Form", save:"Save Report",
      allTitle:"All Reports",
      allDesc:"Search and filter recorded safety reports.",
      export:"Export CSV", allTypes:"All Types",
      allSeverities:"All Severities", allStatuses:"All Statuses",
      status:"Status", manage:"Actions",
      localNote:"Data is stored locally in this browser",
      noResults:"No matching results",
      actionsTitle:"Corrective Actions",
      actionsDesc:"Track owners, due dates, and resolution status.",
      pending:"Pending Actions", overdue:"Overdue Actions",
      completedActions:"Completed Actions", actionRegister:"Action Register",
      actionRegisterDesc:"Actions recorded against reports",
      noActions:"No actions recorded.",
      footer:"Safety is everyone's responsibility",
      print:"Print", close:"Close",
      low:"Low", medium:"Medium", high:"High", critical:"Critical",
      Open:"Open", "In Progress":"In Progress", Closed:"Closed",
      view:"View", edit:"Edit", delete:"Delete",
      confirmDelete:"Delete this report permanently?",
      saved:"Report saved successfully.",
      updated:"Report updated successfully.",
      deleted:"Report deleted.",
      exported:"Reports exported.",
      exportEmpty:"There are no reports to export.",
      notSpecified:"Not specified", reportDetails:"Report Details",
      searchPlaceholder:"Search by ID, title, or site...",
      overdueLabel:"Overdue", closeAction:"Close Action",
      reopen:"Reopen", saveChanges:"Save Changes",
      statusUpdated:"Action status updated."
    }
  };

  let lang = localStorage.getItem("hse-control-language") || "ar";
  let reports = loadReports();
  let editingId = null;

  function loadReports() {
    try {
      const data = JSON.parse(localStorage.getItem(STORAGE) || "[]");
      return Array.isArray(data) ? data : [];
    } catch {
      return [];
    }
  }

  function saveData() {
    try {
      localStorage.setItem(STORAGE, JSON.stringify(reports));
      return true;
    } catch (error) {
      toast("تعذر حفظ البيانات في المتصفح.", true);
      console.error(error);
      return false;
    }
  }

  function t(key) {
    return langData[lang]?.[key] || langData.ar[key] || key;
  }

  function esc(value) {
    return String(value ?? "").replace(/[&<>"']/g, c => ({
      "&":"&amp;", "<":"&lt;", ">":"&gt;",
      '"':"&quot;", "'":"&#39;"
    })[c]);
  }

  function toast(message, error = false) {
    const el = $("#toast");
    if (!el) return;

    el.textContent = message;
    el.classList.toggle("error", error);
    el.style.display = "block";

    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => {
      el.style.display = "none";
    }, 3000);
  }

  function makeId() {
    const max = reports.reduce((n, r) => {
      const match = String(r.id).match(/(\d+)$/);
      return match ? Math.max(n, Number(match[1])) : n;
    }, 0);

    return "HSE-" + String(max + 1).padStart(4, "0");
  }

  function formatDate(value, time = false) {
    if (!value) return t("notSpecified");

    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return esc(value);

    return new Intl.DateTimeFormat(
      lang === "ar" ? "ar-LY" : "en-GB",
      {
        year:"numeric",
        month:"short",
        day:"numeric",
        ...(time ? {hour:"2-digit", minute:"2-digit"} : {})
      }
    ).format(d);
  }

  function nowForInput() {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
  }

  function statusBadge(status) {
    const cls = status === "Closed"
      ? "closed"
      : status === "In Progress" ? "progress" : "open";

    return `<span class="badge ${cls}">${esc(t(status || "Open"))}</span>`;
  }

  function severityBadge(severity) {
    const value = severity || "Medium";
    const cls = value.toLowerCase();
    const key = {
      Low:"low", Medium:"medium", High:"high", Critical:"critical"
    }[value] || "medium";

    return `<span class="badge ${cls}">${esc(t(key))}</span>`;
  }

  function showPage(name) {
    const target = $(`#page-${name}`);
    if (!target) return;

    $$(".page").forEach(p => p.classList.toggle("active", p === target));

    $$(".nav[data-page]").forEach(b => {
      b.classList.toggle("active", b.dataset.page === name);
    });

    const titleKeys = {
      dashboard:"dashboard", report:"newReport",
      reports:"reports", actions:"actions"
    };

    $("#pageTitle").textContent = t(titleKeys[name] || "dashboard");
    $(".sidebar")?.classList.remove("open");

    if (name === "reports") renderReports();
    if (name === "actions") renderActions();

    window.scrollTo({top:0, behavior:"smooth"});
  }

  function renderDashboard() {
    const total = reports.length;
    const open = reports.filter(r => r.status !== "Closed").length;
    const high = reports.filter(r =>
      ["High", "Critical"].includes(r.severity) &&
      r.status !== "Closed"
    ).length;
    const closed = reports.filter(r => r.status === "Closed").length;

    $("#total").textContent = total;
    $("#open").textContent = open;
    $("#risk").textContent = high;
    $("#closed").textContent = closed;

    const recent = [...reports]
      .sort((a,b) => new Date(b.createdAt || b.date) -
                     new Date(a.createdAt || a.date))
      .slice(0,5);

    $("#recentTable").innerHTML = recent.map(r => `
      <tr>
        <td class="id">${esc(r.id)}</td>
        <td class="title-cell">${esc(r.title)}</td>
        <td>${esc(r.site)}</td>
        <td>${severityBadge(r.severity)}</td>
        <td>${statusBadge(r.status)}</td>
      </tr>
    `).join("");

    $("#recentEmpty").classList.toggle("hidden", recent.length > 0);

    const levels = [
      {key:"low", value:"Low", color:"#18866a"},
      {key:"medium", value:"Medium", color:"#d7a72b"},
      {key:"high", value:"High", color:"#ed9c3b"},
      {key:"critical", value:"Critical", color:"#d94b4b"}
    ];

    $("#riskBars").innerHTML = levels.map(item => {
      const count = reports.filter(r => r.severity === item.value).length;
      const percent = total ? Math.round(count / total * 100) : 0;

      return `
        <div>
          <div class="risk-label">
            <span>${esc(t(item.key))}</span>
            <span>${count} (${percent}%)</span>
          </div>
          <div class="track">
            <div class="fill"
                 style="width:${percent}%;background:${item.color}"></div>
          </div>
        </div>`;
    }).join("");
  }

  function filteredReports() {
    const query = ($("#search")?.value || "").trim().toLowerCase();
    const type = $("#typeFilter")?.value || "";
    const severity = $("#severityFilter")?.value || "";
    const status = $("#statusFilter")?.value || "";

    return [...reports].filter(r => {
      const text = [
        r.id, r.title, r.site, r.location,
        r.reporter, r.description
      ].join(" ").toLowerCase();

      return (!query || text.includes(query)) &&
        (!type || r.type === type) &&
        (!severity || r.severity === severity) &&
        (!status || r.status === status);
    }).sort((a,b) =>
      new Date(b.createdAt || b.date) -
      new Date(a.createdAt || a.date)
    );
  }

  function renderReports() {
    const list = filteredReports();

    $("#reportsTable").innerHTML = list.map(r => `
      <tr>
        <td class="id">${esc(r.id)}</td>
        <td>${formatDate(r.date, true)}</td>
        <td>${esc(r.type)}</td>
        <td class="title-cell">${esc(r.title)}</td>
        <td>${esc(r.site)}</td>
        <td>${severityBadge(r.severity)}</td>
        <td>${statusBadge(r.status)}</td>
        <td>
          <button class="action-btn" data-action="view"
                  data-id="${esc(r.id)}">${esc(t("view"))}</button>
          <button class="action-btn" data-action="edit"
                  data-id="${esc(r.id)}">${esc(t("edit"))}</button>
          <button class="action-btn delete" data-action="delete"
                  data-id="${esc(r.id)}">${esc(t("delete"))}</button>
        </td>
      </tr>
    `).join("");

    $("#count").textContent = lang === "ar"
      ? `عدد النتائج: ${list.length}`
      : `Results: ${list.length}`;

    $("#reportsEmpty").classList.toggle("hidden", list.length > 0);
  }

  function isOverdue(r) {
    return Boolean(
      r.dueDate &&
      r.status !== "Closed" &&
      new Date(r.dueDate + "T23:59:59") < new Date()
    );
  }

  function renderActions() {
    const list = reports.filter(r =>
      (r.correctiveAction || "").trim() ||
      r.responsible || r.dueDate
    );

    const pending = list.filter(r => r.status !== "Closed");
    const overdue = pending.filter(isOverdue);
    const completed = list.filter(r => r.status === "Closed");

    $("#pendingActions").textContent = pending.length;
    $("#overdueActions").textContent = overdue.length;
    $("#completedActions").textContent = completed.length;

    $("#actionsTable").innerHTML = list.map(r => `
      <tr>
        <td class="id">${esc(r.id)}</td>
        <td class="title-cell">${esc(r.correctiveAction || t("notSpecified"))}</td>
        <td>${esc(r.responsible || t("notSpecified"))}</td>
        <td>${formatDate(r.dueDate)}</td>
        <td>
          ${statusBadge(r.status)}
          ${isOverdue(r)
            ? `<span class="badge critical">${esc(t("overdueLabel"))}</span>`
            : ""}
        </td>
        <td>
          ${r.status !== "Closed"
            ? `<button class="action-btn" data-action="progress"
                data-id="${esc(r.id)}"> ${esc(t("In Progress"))}</button>
               <button class="action-btn" data-action="close"
                data-id="${esc(r.id)}">${esc(t("closeAction"))}</button>`
            : `<button class="action-btn" data-action="reopen"
                data-id="${esc(r.id)}">${esc(t("reopen"))}</button>`
          }
          <button class="action-btn" data-action="view"
            data-id="${esc(r.id)}">${esc(t("view"))}</button>
        </td>
      </tr>
    `).join("");

    $("#actionsEmpty").classList.toggle("hidden", list.length > 0);
  }

  function renderAll() {
    renderDashboard();
    renderReports();
    renderActions();
  }

  function resetForm() {
    $("#reportForm").reset();
    editingId = null;
    $("#reportDate").value = nowForInput();
    $('button[type="submit"]', $("#reportForm")).textContent = t("save");
  }

  function saveReport(event) {
    event.preventDefault();

    const form = $("#reportForm");
    if (!form.reportValidity()) return;

    const data = Object.fromEntries(new FormData(form).entries());

    if (editingId) {
      const index = reports.findIndex(r => r.id === editingId);
      if (index < 0) return;

      reports[index] = {
        ...reports[index],
        ...data,
        updatedAt:new Date().toISOString()
      };
    } else {
      reports.push({
        ...data,
        id:makeId(),
        status:"Open",
        createdAt:new Date().toISOString()
      });
    }

    const wasEditing = Boolean(editingId);
    if (!saveData()) return;

    resetForm();
    renderAll();
    toast(t(wasEditing ? "updated" : "saved"));
    showPage("reports");
  }

  function editReport(id) {
    const r = reports.find(item => item.id === id);
    if (!r) return;

    editingId = id;
    showPage("report");

    const form = $("#reportForm");

    Object.entries(r).forEach(([key, value]) => {
      const field = form.elements.namedItem(key);
      if (field && "value" in field) field.value = value ?? "";
    });

    $('button[type="submit"]', form).textContent = t("saveChanges");
    $("#pageTitle").textContent = t("edit");
  }

  function deleteReport(id) {
    if (!confirm(t("confirmDelete"))) return;

    reports = reports.filter(r => r.id !== id);
    if (!saveData()) return;

    renderAll();
    toast(t("deleted"));
  }

  function detailItem(label, value, full = false) {
    return `
      <div class="detail-item${full ? " full" : ""}">
        <small>${esc(label)}</small>
        <div>${esc(value || t("notSpecified"))}</div>
      </div>`;
  }

  function viewReport(id) {
    const r = reports.find(item => item.id === id);
    if (!r) return;

    $("#detailsTitle").textContent = `${t("reportDetails")} — ${r.id}`;

    $("#detailsBody").innerHTML = `
      <div class="detail-grid">
        ${detailItem("ID", r.id)}
        ${detailItem(t("date"), formatDate(r.date, true))}
        ${detailItem(t("type"), r.type)}
        ${detailItem(t("status"), t(r.status))}
        ${detailItem(t("site"), r.site)}
        ${detailItem(t("location"), r.location)}
        ${detailItem(t("department"), r.department)}
        ${detailItem(t("reporter"), r.reporter)}
        ${detailItem(t("title"), r.title, true)}
        ${detailItem(t("category"), r.category)}
        ${detailItem(t("severity"), t((r.severity || "Medium").toLowerCase()))}
        ${detailItem(t("description"), r.description, true)}
        ${detailItem(t("immediate"), r.immediateAction, true)}
        ${detailItem(t("corrective"), r.correctiveAction, true)}
        ${detailItem(t("responsible"), r.responsible)}
        ${detailItem(t("dueDate"), formatDate(r.dueDate))}
      </div>`;

    const dialog = $("#detailsDialog");
    if (dialog.showModal) dialog.showModal();
    else dialog.setAttribute("open", "");
  }

  function exportCSV() {
    if (!reports.length) {
      toast(t("exportEmpty"), true);
      return;
    }

    const columns = [
      "id", "date", "type", "title", "site", "location",
      "department", "reporter", "category", "severity",
      "description", "immediateAction", "correctiveAction",
      "responsible", "dueDate", "status"
    ];

    const csvCell = value =>
      `"${String(value ?? "").replace(/"/g, '""')}"`;

    const csv = "\uFEFF" + [
      columns.join(","),
      ...reports.map(r =>
        columns.map(key => csvCell(r[key])).join(",")
      )
    ].join("\r\n");

    const blob = new Blob([csv], {
      type:"text/csv;charset=utf-8;"
    });

    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");

    a.href = url;
    a.download = `HSE-Reports-${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);

    toast(t("exported"));
  }

  function setLanguage(next) {
    lang = next;
    localStorage.setItem("hse-control-language", lang);

    document.documentElement.lang = lang;
    document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";
    document.body.dir = document.documentElement.dir;

    $("#languageBtn").textContent = lang === "ar" ? "English" : "العربية";

    $$("[data-i18n]").forEach(el => {
      const key = el.dataset.i18n;
      if (langData[lang][key]) el.textContent = langData[lang][key];
    });

    if ($("#search")) $("#search").placeholder = t("searchPlaceholder");

    $("#today").textContent = formatDate(new Date().toISOString());

    const current = $(".page.active")?.id.replace("page-", "") || "dashboard";
    const titleKeys = {
      dashboard:"dashboard", report:"newReport",
      reports:"reports", actions:"actions"
    };
    $("#pageTitle").textContent = t(titleKeys[current] || "dashboard");

    renderAll();
  }

  function handleAction(event) {
    const button = event.target.closest("[data-action]");
    if (!button) return;

    const {action, id} = button.dataset;

    if (action === "view") return viewReport(id);
    if (action === "edit") return editReport(id);
    if (action === "delete") return deleteReport(id);

    const report = reports.find(r => r.id === id);
    if (!report) return;

    if (action === "progress") report.status = "In Progress";
    if (action === "close") report.status = "Closed";
    if (action === "reopen") report.status = "Open";

    report.updatedAt = new Date().toISOString();

    if (saveData()) {
      renderAll();
      toast(t("statusUpdated"));
    }
  }

  function init() {
    $("#year").textContent = new Date().getFullYear();
    $("#today").textContent = formatDate(new Date().toISOString());
    $("#reportDate").value = nowForInput();

    $$(".nav[data-page]").forEach(button => {
      button.addEventListener("click", () => showPage(button.dataset.page));
    });

    $$("[data-goto]").forEach(button => {
      button.addEventListener("click", () => {
        if (button.dataset.goto === "report") resetForm();
        showPage(button.dataset.goto);
      });
    });

    $("#languageBtn").addEventListener("click", () => {
      setLanguage(lang === "ar" ? "en" : "ar");
    });

    $("#menuBtn")?.addEventListener("click", () => {
      $(".sidebar").classList.toggle("open");
    });

    $("#reportForm").addEventListener("submit", saveReport);

    $("#reportForm").addEventListener("reset", () => {
      setTimeout(() => {
        editingId = null;
        $("#reportDate").value = nowForInput();
        $('button[type="submit"]', $("#reportForm")).textContent = t("save");
      }, 0);
    });

    ["search", "typeFilter", "severityFilter", "statusFilter"].forEach(id => {
      $(`#${id}`)?.addEventListener("input", renderReports);
      $(`#${id}`)?.addEventListener("change", renderReports);
    });

    $("#reportsTable").addEventListener("click", handleAction);
    $("#actionsTable").addEventListener("click", handleAction);

    $("#exportBtn").addEventListener("click", exportCSV);

    $("#closeDialog").addEventListener("click", () => $("#detailsDialog").close());
    $("#closeDialog2").addEventListener("click", () => $("#detailsDialog").close());

    $("#printBtn").addEventListener("click", () => {
      const style = document.createElement("style");
      style.id = "hse-print-style";
      style.textContent = `
        @media print {
          body * { visibility:hidden !important; }
          #detailsDialog, #detailsDialog * { visibility:visible !important; }
          #detailsDialog {
            position:absolute !important;
            inset:0 auto auto 0 !important;
            width:100% !important;
            max-width:none !important;
            max-height:none !important;
            overflow:visible !important;
            box-shadow:none !important;
          }
          #detailsDialog .dialog-footer,
          #detailsDialog #closeDialog { display:none !important; }
        }`;

      document.head.appendChild(style);
      window.addEventListener("afterprint", () => style.remove(), {once:true});
      window.print();
      setTimeout(() => style.remove(), 1500);
    });

    $("#detailsDialog").addEventListener("click", event => {
      if (event.target === $("#detailsDialog")) {
        $("#detailsDialog").close();
      }
    });

    setLanguage(lang);
    renderAll();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, {once:true});
  } else {
    init();
  }
})();