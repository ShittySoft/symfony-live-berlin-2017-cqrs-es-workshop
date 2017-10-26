Feature: checking in and checking out

  Scenario: check-in
    Given a building was registered
    When the user checks into the building
    Then the user was checked into the building

  Scenario: double check-in causes security anomalies to be raised
    Given a building was registered
    And the user checked into the building
    When the user checks into the building
    Then the user was checked into the building
    And a check-in anomaly was detected

  Scenario: bob checks in
    Given "Golden Tulip" was registered as a building
    When "bob" checks into "Golden Tulip"
    Then "bob" was checked into "Golden Tulip"
