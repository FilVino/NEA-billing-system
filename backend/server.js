// server.js

const express = require("express");
const mysql = require("mysql");
const crypto = require("crypto");
const cors = require("cors");

const app = express();

// =========================
// MIDDLEWARE
// =========================
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// =========================
// DATABASE
// =========================
const db = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "billing_system"
});

db.connect((err) => {
    if (err) {
        console.log("DB ERROR:", err);
    } else {
        console.log("🚀 Database Connected");
    }
});

// =========================
// REGISTER (FIXED)
// =========================
app.post("/register", (req, res) => {

    const name = (req.body.name || "").trim();
    const username = (req.body.username || "").trim();
    const password = (req.body.password || "").trim();

    if (!name || !username || !password) {
        return res.json({
            success: false,
            message: "All fields required"
        });
    }

    const hashed = crypto.createHash("md5").update(password).digest("hex");

    const sql = `
        INSERT INTO users (username, email, password, user_type)
        VALUES (?, ?, ?, 'user')
    `;

    db.query(sql, [username, name, hashed], (err) => {

        if (err) {
            return res.json({
                success: false,
                message: err.message
            });
        }

        res.json({
            success: true,
            message: "Registered successfully"
        });
    });
});

// =========================
// LOGIN
// =========================
app.post("/login", (req, res) => {

    const username = (req.body.username || "").trim();
    const password = (req.body.password || "").trim();

    if (!username || !password) {
        return res.json({
            success: false,
            message: "Required fields missing"
        });
    }

    const hashed = crypto.createHash("md5").update(password).digest("hex");

    const sql = `
        SELECT * FROM users
        WHERE username=? AND password=?
        LIMIT 1
    `;

    db.query(sql, [username, hashed], (err, result) => {

        if (err) return res.json({ success: false, message: err.message });

        if (result.length === 0) {
            return res.json({
                success: false,
                message: "Invalid credentials"
            });
        }

        const user = result[0];

        res.json({
            success: true,
            user: {
                id: user.id,
                username: user.username,
                email: user.email,
                user_type: user.user_type
            }
        });
    });
});

// =========================
// CUSTOMER SEARCH (OPTIONAL)
// =========================
app.get("/customer/search", (req, res) => {

    const name = req.query.name || "";
    const mobile = req.query.mobile || "";

    const sql = `
        SELECT * FROM customer
        WHERE Fullname LIKE ?
        AND MobileNo LIKE ?
    `;

    db.query(sql, [`%${name}%`, `%${mobile}%`], (err, result) => {

        if (err) return res.json({ success: false, message: err.message });

        res.json({ success: true, data: result });
    });
});

// =========================
// DASHBOARD API (MAIN FIX)
// =========================
app.get("/dashboard/customer", (req, res) => {

    const name = req.query.name || "";
    const mobile = req.query.mobile || "";

    const sql = `
        SELECT 
            c.CUSID,
            c.Fullname,
            c.AddressName,
            c.MobileNo,
            d.descrip AS demand_type
        FROM customer c
        LEFT JOIN demandtype d
        ON c.demand_type_id = d.demand_type_id
        WHERE c.Fullname LIKE ?
        AND c.MobileNo LIKE ?
        LIMIT 1
    `;

    db.query(sql, [`%${name}%`, `%${mobile}%`], (err, result) => {

        if (err) return res.json({ success: false, message: err.message });

        if (result.length === 0) {
            return res.json({ success: false, message: "No customer found" });
        }

        const customer = result[0];

        const billSql = `
            SELECT
                BID,
                BDate,
                BYear,
                BMonth,
                Current_Reading,
                Prev_Reading,
                Bamount,
                payment_status
            FROM bill
            WHERE CUSID = ?
            ORDER BY BYear DESC, BID DESC
        `;

        db.query(billSql, [customer.CUSID], (err2, bills) => {

            if (err2) return res.json({ success: false, message: err2.message });

            // FORCE 0/1 ONLY
            const cleanBills = bills.map(b => ({
                ...b,
                payment_status: Number(b.payment_status)
            }));

            res.json({
                success: true,
                customer,
                bills: cleanBills
            });
        });
    });
});

// =========================
// USERS
// =========================
app.get("/users", (req, res) => {
    db.query("SELECT * FROM users", (err, result) => {
        if (err) return res.json(err);
        res.json(result);
    });
});

// =========================
// CUSTOMER
// =========================
app.get("/customer", (req, res) => {
    db.query("SELECT * FROM customer", (err, result) => {
        if (err) return res.json(err);
        res.json(result);
    });
});
// =========================
// GET CUSTOMER BY ID
// =========================
app.get("/customer/:id", (req, res) => {

    const cusid = req.params.id;

    const customerSql = `
        SELECT 
            c.CUSID,
            c.FullName,
            c.MobileNo,
            c.AddressName,
            d.descrip AS demand_type,
            b.branch_name
        FROM customer c
        LEFT JOIN demandtype d ON c.demand_type_id = d.demand_type_id
        LEFT JOIN branch b ON c.BranchId = b.branch_id
        WHERE c.CUSID = ?
        LIMIT 1
    `;

    db.query(customerSql, [cusid], (err, customerResult) => {

        if (err) return res.json({ success: false, message: err.message });

        if (customerResult.length === 0) {
            return res.json({ success: false, message: "Customer not found" });
        }

        const customer = customerResult[0];

        const billSql = `
            SELECT
                BID,
                BDate,
                BYear,
                BMonth,
                Current_Reading,
                Prev_Reading,
                Bamount,
                payment_status
            FROM bill
            WHERE CUSID = ?
            ORDER BY BYear DESC, BID DESC
        `;

        db.query(billSql, [cusid], (err2, bills) => {

            if (err2) return res.json({ success: false, message: err2.message });

            const cleanBills = bills.map(b => ({
                ...b,
                payment_status: Number(b.payment_status)
            }));

            res.json({
                success: true,
                customer,
                bills: cleanBills
            });
        });
    });
});
// =========================
// CREATE PAYMENT
// =========================
app.post("/payment/create", (req, res) => {

    const {
        bill_id,
        amount,
        payment_option_id,
        rebate_amt,
        fine_amt
    } = req.body;

    const sql = `
        INSERT INTO payment 
        (BID, PAmount, Payment_Option_Id, Rebeat_Amt, Fine_Amt)
        VALUES (?, ?, ?, ?, ?)
    `;

    db.query(sql, [
        bill_id,
        amount,
        payment_option_id,
        rebate_amt || 0,
        fine_amt || 0
    ], (err) => {

        if (err) {
            return res.json({ success: false, message: err.message });
        }

        // ✅ AUTO UPDATE BILL STATUS = PAID
        const updateBill = `
            UPDATE bill 
            SET payment_status = 1
            WHERE BID = ?
        `;

        db.query(updateBill, [bill_id]);

        res.json({
            success: true,
            message: "Payment successful"
        });
    });
});
// =========================
// BRANCH
// =========================
app.get("/branch", (req, res) => {
    db.query("SELECT * FROM branch", (err, result) => {
        if (err) return res.json(err);
        res.json(result);
    });
});

// =========================
// BILL BY CUSTOMER (FIXED)
// =========================
app.get("/bill/customer/:id", (req, res) => {

    const id = req.params.id;

    const sql = `
        SELECT * FROM bill
        WHERE CUSID = ?
    `;

    db.query(sql, [id], (err, result) => {

        if (err) {
            return res.json({
                success: false,
                message: err.message
            });
        }

        res.json({
            success: true,
            data: result
        });
    });
});

// =========================
// PAYMENT
// =========================
app.get("/payment", (req, res) => {
    db.query("SELECT * FROM payment", (err, result) => {
        if (err) return res.json(err);
        res.json(result);
    });
});

app.post("/payment/create", (req, res) => {

    const {
        bill_id,
        amount,
        payment_option_id,
        rebate_amt,
        fine_amt
    } = req.body;

    if (!bill_id || !amount || !payment_option_id) {
        return res.json({
            success: false,
            message: "Missing required fields"
        });
    }

    const sql = `
        INSERT INTO payment 
        (BID, PDate, PAmount, Payment_Option_Id, Rebeat_Amt, Fine_Amt)
        VALUES (?, NOW(), ?, ?, ?, ?)
    `;

    db.query(sql, [
        bill_id,
        amount,
        payment_option_id,
        rebate_amt || 0,
        fine_amt || 0
    ], (err) => {

        if (err) {
            return res.json({
                success: false,
                message: err.message
            });
        }

        // ✅ MARK BILL AS PAID
        const updateBill = `
            UPDATE bill 
            SET payment_status = 1
            WHERE BID = ?
        `;

        db.query(updateBill, [bill_id]);

        res.json({
            success: true,
            message: "Payment successful"
        });
    });
});
// =========================
// PAYMENT OPTIONS (GET ALL)
// =========================
app.get("/payment-methods", (req, res) => {

    const sql = `
        SELECT 
            POID,
            paymentMethod,
            currStatus
        FROM payment_option
    `;

    db.query(sql, (err, result) => {

        if (err) {
            return res.json({
                success: false,
                message: err.message
            });
        }

        res.json({
            success: true,
            data: result
        });
    });

});
// =========================
// START SERVER
// =========================
app.listen(3000, () => {
    console.log("🚀 Billing System running on port 3000");
});