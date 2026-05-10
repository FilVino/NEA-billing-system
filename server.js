const express = require("express");
const mysql = require("mysql");
const cors = require("cors");
const http = require("http");
const https = require("https");
const crypto = require("crypto");
const { URL } = require("url");

const app = express();

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const apacheTarget = new URL("http://localhost/nea-billing-system");

function proxyToApache(req, res) {
  const protocol = apacheTarget.protocol === "https:" ? https : http;
  const proxyPath = `${apacheTarget.pathname.replace(/\/$/, "")}${req.originalUrl}`;
  const options = {
    hostname: apacheTarget.hostname,
    port: apacheTarget.port || (apacheTarget.protocol === "https:" ? 443 : 80),
    path: proxyPath,
    method: req.method,
    headers: {
      ...req.headers,
      host: apacheTarget.host,
    },
  };

  const proxyReq = protocol.request(options, (proxyRes) => {
    res.writeHead(proxyRes.statusCode, proxyRes.headers);
    proxyRes.pipe(res, { end: true });
  });

  proxyReq.on("error", (err) => {
    console.error("Proxy error:", err);
    res.status(502).send("Bad gateway: unable to reach PHP web server");
  });

  req.pipe(proxyReq, { end: true });
}

// =======================
// DB CONNECTION
// =======================
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "billing_system"
});

db.connect(err => {
  if (err) console.log("DB Error:", err);
  else console.log("DB Connected to billing_system");
});


// =======================
// HEALTH CHECK
// =======================
app.get("/health", (req, res) => {
  res.json({ success: true, message: "Server online" });
});

// =======================
// USERS (LOGIN)
// =======================
app.post("/login", (req, res) => {
  const { username, password } = req.body;
  const hashedPassword = crypto.createHash("md5").update(password || "").digest("hex");

  const sql = "SELECT * FROM users WHERE username=? AND password=?";

  db.query(sql, [username, hashedPassword], (err, result) => {
    if (err) return res.json({ success: false, message: err.message });

    if (result.length > 0) {
      return res.json({
        success: true,
        user: result[0]
      });
    } else {
      return res.status(401).json({
        success: false,
        message: "Invalid credentials"
      });
    }
  });
});

// =======================
// USERS (REGISTER)
// =======================
app.post("/register", (req, res) => {
  const { username, email, password, user_type } = req.body;

  if (!username || !email || !password) {
    return res.status(400).json({ success: false, message: "Username, email, and password are required" });
  }

  const hashedPassword = crypto.createHash("md5").update(password).digest("hex");

  const checkSql = "SELECT * FROM users WHERE username=? OR email=?";
  db.query(checkSql, [username, email], (checkErr, checkResult) => {
    if (checkErr) return res.json({ success: false, message: checkErr.message });

    if (checkResult.length > 0) {
      return res.status(409).json({ success: false, message: "Username or email already exists" });
    }

    const type = user_type ? user_type : "user";
    const insertSql = "INSERT INTO users (username, email, user_type, password) VALUES (?, ?, ?, ?)";

    db.query(insertSql, [username, email, type, hashedPassword], (insertErr, insertResult) => {
      if (insertErr) return res.json({ success: false, message: insertErr.message });

      const userId = insertResult.insertId;
      db.query("SELECT * FROM users WHERE id = ?", [userId], (userErr, userResult) => {
        if (userErr) return res.json({ success: false, message: userErr.message });
        return res.json({ success: true, user: userResult[0] });
      });
    });
  });
});


// =======================
// USERS (GET ALL)
// =======================
app.get("/users", (req, res) => {
  db.query("SELECT * FROM users", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, users: result });
  });
});


// =======================
// BRANCH
// =======================
app.get("/branch", (req, res) => {
  db.query("SELECT * FROM branch", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, branches: result });
  });
});

app.post("/branch", (req, res) => {
  const { branch_name, currStatus } = req.body;
  if (!branch_name) return res.status(400).json({ success: false, message: "Branch name is required" });

  const sql = "INSERT INTO branch (branch_name, currStatus) VALUES (?, ?)";
  db.query(sql, [branch_name, currStatus ? 1 : 0], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, branch_id: result.insertId });
  });
});


// =======================
// CUSTOMER
// =======================
app.get("/customer", (req, res) => {
  db.query("SELECT * FROM customer", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, customers: result });
  });
});

app.get("/customer/:id", (req, res) => {
  db.query("SELECT * FROM customer WHERE CUSID = ?", [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, customer: result.length ? result[0] : null });
  });
});

app.post("/customer", (req, res) => {
  const { SCND, Fullname, AddressName, MobileNo, BranchId, demand_type_id } = req.body;
  if (!SCND || !Fullname || !AddressName || !MobileNo || !BranchId || !demand_type_id) {
    return res.status(400).json({ success: false, message: "All customer fields are required" });
  }

  const sql = "INSERT INTO customer (SCND, Fullname, AddressName, MobileNo, BranchId, demand_type_id) VALUES (?, ?, ?, ?, ?, ?)";
  db.query(sql, [SCND, Fullname, AddressName, MobileNo, BranchId, demand_type_id], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, CUSID: result.insertId });
  });
});


// =======================
// DEMAND TYPE
// =======================
app.get("/demand-type", (req, res) => {
  db.query("SELECT * FROM demandtype", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, demand_types: result });
  });
});

app.post("/demand-type", (req, res) => {
  const { demand_type_id, descrip, currStatus } = req.body;
  if (!demand_type_id || !descrip) {
    return res.status(400).json({ success: false, message: "Demand type ID and description are required" });
  }

  const checkSql = "SELECT demand_type_id FROM demandtype WHERE demand_type_id = ? OR descrip = ?";
  db.query(checkSql, [demand_type_id, descrip], (checkErr, checkResult) => {
    if (checkErr) return res.status(500).json({ success: false, message: checkErr.message });
    if (checkResult.length > 0) {
      return res.status(409).json({ success: false, message: "Demand type ID or description already exists" });
    }

    const sql = "INSERT INTO demandtype (demand_type_id, descrip, currStatus) VALUES (?, ?, ?)";
    db.query(sql, [demand_type_id, descrip, currStatus ? 1 : 0], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: err.message });
      res.json({ success: true, demand_type_id: result.insertId });
    });
  });
});


// =======================
// DEMAND RATE
// =======================
app.get("/demand-rate", (req, res) => {
  db.query("SELECT * FROM demandrate", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, demand_rates: result });
  });
});

app.post("/demand-rate", (req, res) => {
  const { demand_rate, effective_rate, is_current, demand_type_id } = req.body;
  if (!demand_rate || !effective_rate || !demand_type_id) {
    return res.status(400).json({ success: false, message: "All demand rate fields are required" });
  }

  const proceed = () => {
    const sql = "INSERT INTO demandrate (demand_rate, effective_rate, is_current, demand_type_id) VALUES (?, ?, ?, ?)";
    db.query(sql, [demand_rate, effective_rate, is_current ? 1 : 0, demand_type_id], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: err.message });
      res.json({ success: true, demand_rate_id: result.insertId });
    });
  };

  if (is_current) {
    const deactivateSql = "UPDATE demandrate SET is_current = 0 WHERE demand_type_id = ?";
    db.query(deactivateSql, [demand_type_id], (deactivateErr) => {
      if (deactivateErr) return res.status(500).json({ success: false, message: deactivateErr.message });
      proceed();
    });
  } else {
    proceed();
  }
});


// =======================
// PAYMENT OPTION
// =======================
app.post("/payment-option", (req, res) => {
  const { POID, paymentMethod, currStatus } = req.body;
  if (!POID || !paymentMethod) {
    return res.status(400).json({ success: false, message: "Payment option ID and method are required" });
  }

  const checkSql = "SELECT POID FROM payment_option WHERE POID = ? OR paymentMethod = ?";
  db.query(checkSql, [POID, paymentMethod], (checkErr, checkResult) => {
    if (checkErr) return res.status(500).json({ success: false, message: checkErr.message });
    if (checkResult.length > 0) {
      return res.status(409).json({ success: false, message: "Payment option ID or method already exists" });
    }

    const sql = "INSERT INTO payment_option (POID, paymentMethod, currStatus) VALUES (?, ?, ?)";
    db.query(sql, [POID, paymentMethod, currStatus ? 1 : 0], (err, result) => {
      if (err) return res.status(500).json({ success: false, message: err.message });
      res.json({ success: true, POID: result.insertId });
    });
  });
});

app.get("/payment-option", (req, res) => {
  db.query("SELECT * FROM payment_option", (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, payment_options: result });
  });
});


// =======================
// BILL
// =======================
app.get("/bill", (req, res) => {
  const sql = `
    SELECT b.*, c.Fullname AS customer_name, c.MobileNo AS customer_mobile, br.branch_name
    FROM bill b
    LEFT JOIN customer c ON b.CUSID = c.CUSID
    LEFT JOIN branch br ON c.BranchId = br.branch_id
  `;

  db.query(sql, (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, bills: result });
  });
});

app.post("/bill", (req, res) => {
  const { BDate, BYear, BMonth, CUSID, Current_Reading, Prev_Reading, Bamount } = req.body;
  if (!BDate || !BYear || !BMonth || !CUSID || Current_Reading == null || Prev_Reading == null || Bamount == null) {
    return res.status(400).json({ success: false, message: "All bill fields are required" });
  }

  const sql = "INSERT INTO bill (BDate, BYear, BMonth, CUSID, Current_Reading, Prev_Reading, Bamount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
  db.query(sql, [BDate, BYear, BMonth, CUSID, Current_Reading, Prev_Reading, Bamount], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, BID: result.insertId });
  });
});


// =======================
// PAYMENT
// =======================
app.get("/payment", (req, res) => {
  const sql = `
    SELECT p.*, b.Bamount, b.payment_status, po.paymentMethod
    FROM payment p
    LEFT JOIN bill b ON p.BID = b.BID
    LEFT JOIN payment_option po ON p.Payment_Option_Id = po.POID
  `;
  db.query(sql, (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });
    res.json({ success: true, payments: result });
  });
});

app.post("/payment", (req, res) => {
  const { BID, PDate, PAmount, Payment_Option_Id, Rebeat_Amt, Fine_Amt } = req.body;
  if (!BID || !PDate || PAmount == null || !Payment_Option_Id) {
    return res.status(400).json({ success: false, message: "Bill ID, payment date, amount and payment option are required" });
  }

  const sql = "INSERT INTO payment (BID, PDate, PAmount, Payment_Option_Id, Rebeat_Amt, Fine_Amt) VALUES (?, ?, ?, ?, ?, ?)";
  db.query(sql, [BID, PDate, PAmount, Payment_Option_Id, Rebeat_Amt || 0, Fine_Amt || 0], (err, result) => {
    if (err) return res.status(500).json({ success: false, message: err.message });

    const updateSql = "UPDATE bill SET payment_status = 1 WHERE BID = ?";
    db.query(updateSql, [BID], (updateErr) => {
      if (updateErr) return res.status(500).json({ success: false, message: updateErr.message });
      res.json({ success: true, PID: result.insertId });
    });
  });
});


// =======================
// FALLBACK / PHP WEB PROXY
// =======================
app.use((req, res, next) => {
  if (req.path === "/health") {
    return next();
  }

  // Proxy all unknown routes to the Apache/PHP web server.
  // Keep this below all API route definitions.
  proxyToApache(req, res);
});

app.use((err, req, res, next) => {
  console.error("Server error:", err);
  res.status(500).json({ success: false, message: "Internal server error" });
});


// =======================
// START SERVER
// =======================
app.listen(3000, () => {
  console.log("Billing System running on port 3000");
});