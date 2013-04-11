<?php

header("Content-type: application/json");
echo '{
  "apiVersion": "1.0",
  "swaggerVersion": "1.0",
  "basePath": "http://' . $_SERVER['SERVER_NAME'] . '/",
  "apis": [
    {"path": "/user"},
     {"path": "/restaurants"},
     {"path": "/meals"},
     {"path": "/ratings"},
      {"path": "/feedbacks"},
      {"path": "/reports"}
  ]
}';