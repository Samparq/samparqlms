/**
 * Created by saurabh on 04/06/2018.
 */


const sequelize = require("sequelize");

// const connection = new sequelize("samparq-lms", "root", "", {
//     host:"localhost",
//     dialect:"mysql",
//     timezone : "Asia/Kolkata",
// });


const connection = new sequelize("samparq_lms", "root", "$ecurity@123", {
    host:"45.114.141.55",
    dialect:"mysql",
    timezone : "Asia/Kolkata",
});



// const connection2 = new sequelize("cosec_data", "root", "", {
//     host:"localhost",
//     dialect:"mysql",
//     timezone : "Asia/Kolkata",
// });

const connection2 = new sequelize("qdegrees_cosec", "qdegrees_cosec", "cosec@123$", {
    host:"103.231.209.72",
    dialect:"mysql",
    timezone : "Asia/Kolkata",
});


//live credentials
// const connection = new sequelize("qdegrees_qds_sampark", "qdegrees_sampark", "admin@123", {
//     host:"103.231.209.72",
//     dialect:"mysql",
//     timezone : "Asia/Kolkata",
// });

module.exports = {
    Sequelize:sequelize,
    Connection:connection,
    Connection2:connection2
};