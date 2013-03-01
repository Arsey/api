<?php

header("Content-type: application/json");
echo '{
  "apiVersion": "1.0",
  "swaggerVersion": "1.0",
  "basePath": "https://' . $_SERVER['SERVER_NAME'] . '/documentation/",
  "apis": [
    {"path": "/user"},
     {"path": "/restaurants"},
     {"path": "/meals"},
      {"path": "/feedbacks"},
      {"path": "/reports"},
      {"path": "/ratings"}
  ]
}';