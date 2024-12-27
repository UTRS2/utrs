.tracking-detail {
    padding: 3rem 0;
  }
  #tracking {
    margin-bottom: 1rem;
  }
  [class*="tracking-status-"] p {
    margin: 0;
    font-size: 1.1rem;
    color: #fff;
    text-transform: uppercase;
    text-align: center;
  }
  [class*="tracking-status-"] {
    padding: 1.6rem 0;
  }
  .tracking-list {
    border: 1px solid #e5e5e5;
  }
  .tracking-item {
    border-left: 4px solid #00ba0d;
    position: relative;
    padding: 2rem 1.5rem 0.5rem 2.5rem;
    font-size: 0.9rem;
    margin-left: 3rem;
    min-height: 5rem;
  }
  .tracking-item:last-child {
    padding-bottom: 4rem;
  }
  .tracking-item .tracking-date {
    margin-bottom: 0.5rem;
  }
  .tracking-item .tracking-date span {
    color: #888;
    font-size: 85%;
    padding-left: 0.4rem;
  }
  .tracking-item .tracking-content {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
  }
  .tracking-item .tracking-content span {
    display: block;
    color: #767676;
    font-size: 13px;
  }
  .tracking-item .tracking-icon {
    position: absolute;
    left: -0.7rem;
    width: 1.1rem;
    height: 1.1rem;
    text-align: center;
    border-radius: 50%;
    font-size: 1.1rem;
    background-color: #fff;
    color: #fff;
  }
  
  .tracking-item-error {
    border-left: 4px solid #ff0000;
    position: relative;
    padding: 2rem 1.5rem 0.5rem 2.5rem;
    font-size: 0.9rem;
    margin-left: 3rem;
    min-height: 5rem;
  }
  .tracking-item-pending {
    border-left: 4px solid #d6d6d6;
    position: relative;
    padding: 2rem 1.5rem 0.5rem 2.5rem;
    font-size: 0.9rem;
    margin-left: 3rem;
    min-height: 5rem;
  }
  .tracking-item-pending:last-child {
    padding-bottom: 4rem;
  }
  .tracking-item-pending .tracking-date {
    margin-bottom: 0.5rem;
  }
  .tracking-item-pending .tracking-date span {
    color: #888;
    font-size: 85%;
    padding-left: 0.4rem;
  }
  .tracking-item-pending .tracking-content {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
  }
  .tracking-item-pending .tracking-content span {
    display: block;
    color: #767676;
    font-size: 13px;
  }

  .tracking-item .tracking-icon.status-pending {
    width: 1.9rem;
    height: 1.9rem;
    left: -1.1rem;
    color: #00ba0d;
    font-size: 0.6rem;
  }
  
  .tracking-item .tracking-icon.status-current {
    color: #d6d6d6;
    font-size: 0.6rem;
  }

  .tracking-item .tracking-icon.status-intransit {
    color: #00ba0d;
    font-size: 0.6rem;
  }
  
  .tracking-item .tracking-icon.status-error {
    color: #ff0000;
    font-size: 0.6rem;
  }

  @media (min-width: 992px) {
    .tracking-item {
      margin-left: 10rem;
    }
    .tracking-item .tracking-date {
      position: absolute;
      left: -10rem;
      width: 7.5rem;
      text-align: right;
    }
    .tracking-item .tracking-date span {
      display: block;
    }
    .tracking-item .tracking-content {
      padding: 0;
      background-color: transparent;
    }
  
    .tracking-item-pending {
      margin-left: 10rem;
    }
    .tracking-item-pending .tracking-date {
      position: absolute;
      left: -10rem;
      width: 7.5rem;
      text-align: right;
    }
    .tracking-item-pending .tracking-date span {
      display: block;
    }
    .tracking-item-pending .tracking-content {
      padding: 0;
      background-color: transparent;
    }

    .tracking-item-error {
        margin-left: 10rem;
    }
    .tracking-item-error .tracking-date {
        position: absolute;
        left: -10rem;
        width: 7.5rem;
        text-align: right;
    }
    .tracking-item-error .tracking-date span {
        display: block;
    }
    .tracking-item-error .tracking-content {
        padding: 0;
        background-color: transparent;
    }
  }
  
  .tracking-item .tracking-content {
    font-weight: 600;
    font-size: 17px;
  }
  
  .blinker {
    border: 7px solid #e9f8ea;
    animation: blink 1s;
    animation-iteration-count: infinite;
  }
  @keyframes blink { 50% { border-color:#fff ; }  }  